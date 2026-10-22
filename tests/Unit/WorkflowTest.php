<?php

declare(strict_types=1);

/**
 * Copyright 2020-Present The Serverless Workflow Specification Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Serverless\Workflow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Serverless\Workflow\Components;
use Serverless\Workflow\Document;
use Serverless\Workflow\Duration;
use Serverless\Workflow\Endpoint;
use Serverless\Workflow\Task\Call\HttpArguments;
use Serverless\Workflow\Task\CallHttp;
use Serverless\Workflow\Task\SetTask;
use Serverless\Workflow\Task\TryTask;
use Serverless\Workflow\TaskList;
use Serverless\Workflow\Timeout;
use Serverless\Workflow\Workflow;
use Serverless\Workflow\WorkflowValidationException;
use Serverless\Workflow\WorkflowValidator;

final class WorkflowTest extends TestCase
{
    private string $json;

    private string $yaml;

    private Workflow $workflow;

    protected function setUp(): void
    {
        $json = file_get_contents(__DIR__ . '/../Fixtures/workflow.json');
        $yaml = file_get_contents(__DIR__ . '/../Fixtures/workflow.yaml');
        self::assertNotFalse($json);
        self::assertNotFalse($yaml);

        $this->json = $json;
        $this->yaml = $yaml;
        $this->workflow = Workflow::fromYaml($this->yaml);
    }

    public function testBuild(): void
    {
        $workflow = new Workflow([
            'document' => new Document([
                'dsl' => '1.0.1',
                'namespace' => 'examples',
                'name' => 'buy-pet',
                'version' => '0.1.0',
            ]),
            'use' => new Components([
                'timeouts' => [
                    'short' => new Timeout([
                        'after' => new Duration([
                            'seconds' => 30,
                        ]),
                    ]),
                ],
            ]),
            'do' => new TaskList([
                'getPet' => new CallHttp([
                    'with' => new HttpArguments([
                        'method' => 'get',
                        'endpoint' => new Endpoint([
                            'uri' => 'https://petstore.swagger.io/v2/pet/{petId}',
                            'authentication' => [
                                'bearer' => [
                                    'token' => '${ .token }',
                                ],
                            ],
                        ]),
                    ]),
                    'timeout' => 'short',
                ]),
                'markAsSold' => new SetTask([
                    'set' => [
                        'status' => 'sold',
                    ],
                ]),
                'buyPet' => new CallHttp([
                    'with' => [
                        'method' => 'put',
                        'endpoint' => 'https://petstore.swagger.io/v2/pet/{petId}',
                        'body' => '${ . + { status: .status } }',
                    ],
                    'then' => 'end',
                ]),
            ]),
            'timeout' => new Timeout([
                'after' => 'PT5M',
            ]),
        ]);

        self::assertEquals($this->workflow, $workflow);
    }

    public function testToJson(): void
    {
        self::assertSame($this->json, $this->workflow->toJson());
    }

    public function testFromJson(): void
    {
        self::assertEquals($this->workflow, Workflow::fromJson($this->json));
    }

    public function testToYaml(): void
    {
        self::assertSame($this->yaml, $this->workflow->toYaml());
    }

    public function testFromYaml(): void
    {
        self::assertEquals($this->workflow, Workflow::fromYaml($this->yaml));
    }

    public function testTypedAccess(): void
    {
        self::assertNotNull($this->workflow->do);
        $getPet = $this->workflow->do->get('getPet');
        self::assertInstanceOf(CallHttp::class, $getPet);
        self::assertSame('http', $getPet->call);
        self::assertNotNull($getPet->with);
        self::assertSame('get', $getPet->with->method);
        self::assertInstanceOf(Endpoint::class, $getPet->with->endpoint);
        self::assertNotNull($getPet->with->endpoint->authentication);
        self::assertNotNull($getPet->with->endpoint->authentication->bearer);
        self::assertSame('${ .token }', $getPet->with->endpoint->authentication->bearer->token);
        self::assertSame('short', $getPet->timeout);

        $buyPet = $this->workflow->do->get('buyPet');
        self::assertInstanceOf(CallHttp::class, $buyPet);
        self::assertNotNull($buyPet->with);
        self::assertSame('https://petstore.swagger.io/v2/pet/{petId}', $buyPet->with->endpoint);
        self::assertSame('end', $buyPet->then);

        self::assertSame(['getPet', 'markAsSold', 'buyPet'], $this->workflow->do->names());
        self::assertInstanceOf(Timeout::class, $this->workflow->timeout);
        self::assertSame('PT5M', $this->workflow->timeout->after);
        self::assertNotNull($this->workflow->use);
        self::assertNotNull($this->workflow->use->timeouts);
        self::assertInstanceOf(Duration::class, $this->workflow->use->timeouts['short']->after);
        self::assertSame(30, $this->workflow->use->timeouts['short']->after->seconds);
    }

    public function testValidate(): void
    {
        $this->workflow->validate();

        self::assertSame([], WorkflowValidator::errors($this->workflow));
        self::assertSame([], WorkflowValidator::errors($this->workflow->toArray()));
    }

    public function testValidateReportsErrors(): void
    {
        $workflow = Workflow::fromYaml($this->yaml);
        self::assertNotNull($workflow->document);
        $workflow->document->version = null;
        $getPet = $workflow->do?->get('getPet');
        self::assertInstanceOf(CallHttp::class, $getPet);
        self::assertNotNull($getPet->with);
        $getPet->with->setAdditionalProperty('unexpected', true);

        try {
            $workflow->validate();
            self::fail('Expected a validation exception.');
        } catch (WorkflowValidationException $workflowValidationException) {
            $errors = $workflowValidationException->getErrors();
            self::assertArrayHasKey('/document', $errors);
            self::assertStringContainsString('version', implode(' ', $errors['/document']));
            self::assertArrayHasKey('/do/0/getPet/with', $errors);
            self::assertStringContainsString('/document', $workflowValidationException->getMessage());
        }
    }

    public function testPreservesUnknownProperties(): void
    {
        $workflow = Workflow::fromJson(
            '{"document":{"dsl":"1.0.1","namespace":"a","name":"b","version":"1.0.0","x-custom":1},"do":[{"wait":{"wait":"PT1S","x-note":"n"}}]}'
        );

        self::assertNotNull($workflow->document);
        self::assertSame([
            'x-custom' => 1,
        ], $workflow->document->getAdditionalProperties());
        self::assertSame(1, $workflow->toArray()['document']['x-custom']);
        self::assertSame('n', $workflow->toArray()['do'][0]['wait']['x-note']);
        self::assertArrayHasKey('/document', WorkflowValidator::errors($workflow));
    }

    public function testEmptyObjectsSerializeAsObjects(): void
    {
        $workflow = Workflow::fromYaml(
            "document:\n  dsl: '1.0.1'\n  namespace: a\n  name: b\n  version: '1.0.0'\ndo:\n  - tryIt:\n      try:\n        - wait:\n            wait: PT1S\n      catch:\n        retry:\n          backoff:\n            exponential: {}\n"
        );
        $tryIt = $workflow->do?->get('tryIt');

        self::assertInstanceOf(TryTask::class, $tryIt);
        self::assertStringContainsString('"exponential": {}', $workflow->toJson());
        self::assertStringContainsString('exponential: {  }', $workflow->toYaml());
        self::assertSame([], WorkflowValidator::errors($workflow));
    }
}

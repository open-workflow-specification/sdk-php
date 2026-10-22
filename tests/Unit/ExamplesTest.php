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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Serverless\Workflow\Task\Task;
use Serverless\Workflow\Workflow;
use Serverless\Workflow\WorkflowValidator;
use Symfony\Component\Yaml\Yaml;

final class ExamplesTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function examples(): iterable
    {
        $paths = glob(__DIR__ . '/../Fixtures/examples/*.yaml');
        self::assertNotFalse($paths);
        self::assertNotEmpty($paths);

        foreach ($paths as $path) {
            yield basename($path, '.yaml') => [$path];
        }
    }

    #[DataProvider('examples')]
    public function testRoundTripsWithoutLoss(string $path): void
    {
        $yaml = self::read($path);
        $expected = Yaml::parse($yaml);
        $workflow = Workflow::fromYaml($yaml);

        self::assertEquals($expected, $workflow->toArray());
        self::assertEquals($expected, Workflow::fromJson($workflow->toJson())->toArray());
        self::assertEquals($expected, Workflow::fromYaml($workflow->toYaml())->toArray());
    }

    #[DataProvider('examples')]
    public function testConformsToTheSchema(string $path): void
    {
        $workflow = Workflow::fromYaml(self::read($path));

        self::assertSame([], WorkflowValidator::errors($workflow));
        self::assertSame([], WorkflowValidator::errors(Yaml::parse(self::read($path))));
    }

    #[DataProvider('examples')]
    public function testHydratesTypedTasks(string $path): void
    {
        $workflow = Workflow::fromYaml(self::read($path));

        self::assertNotNull($workflow->document);
        self::assertSame(Workflow::DSL_VERSION, $workflow->document->dsl);
        self::assertNotNull($workflow->do);
        self::assertGreaterThan(0, count($workflow->do));
        foreach ($workflow->do as $name => $task) {
            self::assertNotSame('', $name);
            self::assertInstanceOf(Task::class, $task);
        }
    }

    private static function read(string $path): string
    {
        $contents = file_get_contents($path);
        self::assertNotFalse($contents);

        return $contents;
    }
}

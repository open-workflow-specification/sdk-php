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

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Serverless\Workflow\Task\CallAsyncApi;
use Serverless\Workflow\Task\CallFunction;
use Serverless\Workflow\Task\CallGrpc;
use Serverless\Workflow\Task\CallHttp;
use Serverless\Workflow\Task\CallOpenApi;
use Serverless\Workflow\Task\DoTask;
use Serverless\Workflow\Task\EmitTask;
use Serverless\Workflow\Task\ForkTask;
use Serverless\Workflow\Task\ForTask;
use Serverless\Workflow\Task\ListenTask;
use Serverless\Workflow\Task\RaiseTask;
use Serverless\Workflow\Task\RunTask;
use Serverless\Workflow\Task\SetTask;
use Serverless\Workflow\Task\SwitchTask;
use Serverless\Workflow\Task\Task;
use Serverless\Workflow\Task\TryTask;
use Serverless\Workflow\Task\WaitTask;

final class TaskTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed[], class-string<Task>}>
     */
    public static function tasks(): iterable
    {
        yield 'call http' => [[
            'call' => 'http',
            'with' => [
                'method' => 'get',
                'endpoint' => 'https://example.com',
            ],
        ], CallHttp::class];
        yield 'call openapi' => [[
            'call' => 'openapi',
            'with' => [
                'operationId' => 'x',
            ],
        ], CallOpenApi::class];
        yield 'call grpc' => [[
            'call' => 'grpc',
            'with' => [
                'method' => 'x',
            ],
        ], CallGrpc::class];
        yield 'call asyncapi' => [[
            'call' => 'asyncapi',
            'with' => [
                'operation' => 'x',
            ],
        ], CallAsyncApi::class];
        yield 'call function' => [[
            'call' => 'myFunction',
            'with' => [
                'a' => 1,
            ],
        ], CallFunction::class];
        yield 'do' => [[
            'do' => [],
        ], DoTask::class];
        yield 'for' => [[
            'for' => [
                'in' => '.items',
            ],
            'do' => [],
        ], ForTask::class];
        yield 'fork' => [[
            'fork' => [
                'branches' => [],
            ],
        ], ForkTask::class];
        yield 'emit' => [[
            'emit' => [
                'event' => [],
            ],
        ], EmitTask::class];
        yield 'listen' => [[
            'listen' => [
                'to' => [
                    'any' => [],
                ],
            ],
        ], ListenTask::class];
        yield 'raise' => [[
            'raise' => [
                'error' => 'x',
            ],
        ], RaiseTask::class];
        yield 'run' => [[
            'run' => [
                'shell' => [
                    'command' => 'ls',
                ],
            ],
        ], RunTask::class];
        yield 'set' => [[
            'set' => [
                'a' => 1,
            ],
        ], SetTask::class];
        yield 'switch' => [[
            'switch' => [],
        ], SwitchTask::class];
        yield 'try' => [[
            'try' => [],
            'catch' => [],
        ], TryTask::class];
        yield 'wait' => [[
            'wait' => 'PT1S',
        ], WaitTask::class];
    }

    /**
     * @param mixed[] $data
     * @param class-string<Task> $expectedClass
     */
    #[DataProvider('tasks')]
    public function testResolvesTheTaskType(array $data, string $expectedClass): void
    {
        $task = Task::fromArray($data);

        self::assertInstanceOf($expectedClass, $task);
        self::assertSame($expectedClass, Task::resolveTaskClass($data));
        self::assertEquals($data, $task->toArray());
    }

    public function testRejectsUnknownTasks(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Task::fromArray([
            'unknown' => true,
        ]);
    }

    public function testCallTasksKeepTheirCallName(): void
    {
        $task = new CallHttp();
        self::assertSame('http', $task->call);

        $function = new CallFunction([
            'call' => 'log',
        ]);
        self::assertSame('log', $function->call);
    }

    public function testSerializesBaseProperticesAroundTheTaskBody(): void
    {
        $task = Task::fromArray([
            'then' => 'end',
            'set' => [
                'a' => 1,
            ],
            'if' => '.a == 1',
            'output' => [
                'as' => '.a',
            ],
        ]);

        self::assertSame(['if', 'set', 'output', 'then'], array_keys($task->toArray()));
    }
}

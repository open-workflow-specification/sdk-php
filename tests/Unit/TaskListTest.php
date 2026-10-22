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
use PHPUnit\Framework\TestCase;
use Serverless\Workflow\Task\SetTask;
use Serverless\Workflow\Task\WaitTask;
use Serverless\Workflow\TaskList;

final class TaskListTest extends TestCase
{
    public function testAcceptsTheDslForm(): void
    {
        $tasks = TaskList::fromArray([
            [
                'first' => [
                    'wait' => 'PT1S',
                ],
            ],
            [
                'second' => [
                    'set' => [
                        'a' => 1,
                    ],
                ],
            ],
        ]);

        self::assertSame(['first', 'second'], $tasks->names());
        self::assertInstanceOf(WaitTask::class, $tasks->get('first'));
        self::assertInstanceOf(SetTask::class, $tasks->get('second'));
        self::assertCount(2, $tasks);
    }

    public function testAcceptsANameToTaskMap(): void
    {
        $tasks = new TaskList([
            'first' => new WaitTask([
                'wait' => 'PT1S',
            ]),
            'second' => [
                'set' => [
                    'a' => 1,
                ],
            ],
        ]);

        self::assertSame([
            [
                'first' => [
                    'wait' => 'PT1S',
                ],
            ],
            [
                'second' => [
                    'set' => [
                        'a' => 1,
                    ],
                ],
            ],
        ], $tasks->toArray());
    }

    public function testKeepsInsertionOrderAndReplacesByName(): void
    {
        $tasks = new TaskList();
        $tasks->add('b', new WaitTask([
            'wait' => 'PT1S',
        ]));
        $tasks->add('a', new WaitTask([
            'wait' => 'PT2S',
        ]));
        $tasks->add('b', new WaitTask([
            'wait' => 'PT3S',
        ]));

        self::assertSame(['b', 'a'], $tasks->names());
        self::assertTrue($tasks->has('a'));
        self::assertNull($tasks->get('missing'));

        $tasks->remove('a');
        self::assertSame(['b'], $tasks->names());

        $names = [];
        foreach ($tasks as $name => $task) {
            $names[] = $name;
            self::assertInstanceOf(WaitTask::class, $task);
        }

        self::assertSame(['b'], $names);
    }

    public function testRejectsForeignItems(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TaskList([
            'first' => 'not a task',
        ]);
    }
}

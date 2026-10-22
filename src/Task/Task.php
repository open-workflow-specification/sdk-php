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

namespace Serverless\Workflow\Task;

use InvalidArgumentException;
use Serverless\Workflow\BaseObject;
use Serverless\Workflow\Export;
use Serverless\Workflow\Input;
use Serverless\Workflow\Output;
use Serverless\Workflow\Timeout;

/**
 * A discrete unit of work. Hydrating this class from an array resolves the concrete task type from the data.
 */
abstract class Task extends BaseObject
{
    public string|null $if = null;

    public Input|null $input = null;

    public Output|null $output = null;

    public Export|null $export = null;

    public string|Timeout|null $timeout = null;

    /**
     * A flow directive: "continue", "exit", "end" or the name of the task to transition to.
     */
    public string|null $then = null;

    /**
     * @var array<string, mixed>|null
     */
    public array|null $metadata = null;

    /**
     * @param mixed[] $data
     * @return class-string<Task>
     */
    public static function resolveTaskClass(array $data): string
    {
        if (isset($data['call']) && is_string($data['call'])) {
            return match ($data['call']) {
                'http' => CallHttp::class,
                'openapi' => CallOpenApi::class,
                'grpc' => CallGrpc::class,
                'asyncapi' => CallAsyncApi::class,
                default => CallFunction::class,
            };
        }

        if (array_key_exists('for', $data)) {
            return ForTask::class;
        }

        $classes = [
            'do' => DoTask::class,
            'fork' => ForkTask::class,
            'emit' => EmitTask::class,
            'listen' => ListenTask::class,
            'raise' => RaiseTask::class,
            'run' => RunTask::class,
            'set' => SetTask::class,
            'switch' => SwitchTask::class,
            'try' => TryTask::class,
            'wait' => WaitTask::class,
        ];
        foreach ($classes as $key => $class) {
            if (array_key_exists($key, $data)) {
                return $class;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Unable to determine the task type from the properties [%s].',
            implode(', ', array_map('strval', array_keys($data)))
        ));
    }

    public function properties(): array
    {
        $properties = parent::properties();
        $leading = ['if', 'input'];
        $trailing = ['output', 'export', 'timeout', 'then', 'metadata'];
        $ordered = [];

        foreach ($leading as $name) {
            if (array_key_exists($name, $properties)) {
                $ordered[$name] = $properties[$name];
            }
        }

        foreach ($properties as $name => $value) {
            if (! in_array($name, $leading, true) && ! in_array($name, $trailing, true)) {
                $ordered[$name] = $value;
            }
        }

        foreach ($trailing as $name) {
            if (array_key_exists($name, $properties)) {
                $ordered[$name] = $properties[$name];
            }
        }

        return $ordered;
    }

    protected static function resolveClass(array $data): string
    {
        if (static::class !== self::class) {
            return static::class;
        }

        /** @var class-string<static> $class */
        $class = self::resolveTaskClass($data);

        return $class;
    }
}

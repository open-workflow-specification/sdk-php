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

namespace Serverless\Workflow;

use Serverless\Workflow\Hydration\Hydratable;
use Serverless\Workflow\Task\Task;

/**
 * The ordered, named tasks of a workflow, a "do" block, a fork branch set, a "try" block, and so on.
 *
 * @extends NamedList<Task>
 */
final class TaskList extends NamedList implements Hydratable
{
    /**
     * @param mixed[] $tasks
     */
    public function __construct(array $tasks = [])
    {
        parent::__construct(Task::class, $tasks);
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }
}

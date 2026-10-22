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

use Serverless\Workflow\BaseObject;
use Serverless\Workflow\RetryPolicy;
use Serverless\Workflow\TaskList;

/**
 * Defines the errors to catch and what to do with them.
 */
final class TryCatch extends BaseObject
{
    public CatchErrors|null $errors = null;

    public string|null $as = null;

    public string|null $when = null;

    public string|null $exceptWhen = null;

    public string|RetryPolicy|null $retry = null;

    public TaskList|null $do = null;
}

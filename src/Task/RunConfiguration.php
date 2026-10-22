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
use Serverless\Workflow\Task\Run\Container;
use Serverless\Workflow\Task\Run\Script;
use Serverless\Workflow\Task\Run\Shell;
use Serverless\Workflow\Task\Run\SubflowConfiguration;

/**
 * The configuration of the process to execute: exactly one of container, script, shell or workflow.
 */
final class RunConfiguration extends BaseObject
{
    public Container|null $container = null;

    public Script|null $script = null;

    public Shell|null $shell = null;

    public SubflowConfiguration|null $workflow = null;

    public bool|null $await = null;

    /**
     * The process output to return: "stdout" (default), "stderr", "code", "all" or "none".
     */
    public string|null $return = null;
}

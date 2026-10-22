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

use Serverless\Workflow\Authentication\AuthenticationPolicy;
use Serverless\Workflow\Hydration\MapOf;
use Serverless\Workflow\Hydration\NamedListOf;
use Serverless\Workflow\Task\Task;

/**
 * The reusable components of a workflow, the DSL "use" section.
 */
final class Components extends BaseObject
{
    /**
     * @var array<string, AuthenticationPolicy>|null
     */
    #[MapOf(AuthenticationPolicy::class)]
    public array|null $authentications = null;

    /**
     * @var array<string, Error>|null
     */
    #[MapOf(Error::class)]
    public array|null $errors = null;

    /**
     * @var NamedList<Extension>|null
     */
    #[NamedListOf(Extension::class)]
    public NamedList|null $extensions = null;

    /**
     * @var array<string, Task>|null
     */
    #[MapOf(Task::class)]
    public array|null $functions = null;

    /**
     * @var array<string, RetryPolicy>|null
     */
    #[MapOf(RetryPolicy::class)]
    public array|null $retries = null;

    /**
     * @var list<string>|null
     */
    public array|null $secrets = null;

    /**
     * @var array<string, Timeout>|null
     */
    #[MapOf(Timeout::class)]
    public array|null $timeouts = null;

    /**
     * @var array<string, Catalog>|null
     */
    #[MapOf(Catalog::class)]
    public array|null $catalogs = null;
}

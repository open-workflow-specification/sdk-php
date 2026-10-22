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

namespace Serverless\Workflow\Task\Call;

use Serverless\Workflow\Authentication\AuthenticationPolicy;
use Serverless\Workflow\BaseObject;
use Serverless\Workflow\ExternalResource;

/**
 * The AsyncAPI call arguments.
 */
final class AsyncApiArguments extends BaseObject
{
    public ExternalResource|null $document = null;

    public string|null $channel = null;

    public string|null $operation = null;

    public AsyncApiServer|null $server = null;

    public string|null $protocol = null;

    public AsyncApiMessage|null $message = null;

    public AsyncApiSubscription|null $subscription = null;

    public AuthenticationPolicy|null $authentication = null;
}

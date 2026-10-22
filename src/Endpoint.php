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

/**
 * The configuration of an endpoint. Where the DSL allows it, a plain URI or runtime expression string can be used instead.
 */
final class Endpoint extends BaseObject
{
    public string|null $uri = null;

    public AuthenticationPolicy|null $authentication = null;
}

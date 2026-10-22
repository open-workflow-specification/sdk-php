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

namespace Serverless\Workflow\Authentication;

use Serverless\Workflow\BaseObject;

/**
 * An authentication policy: a reference to a reusable policy (use) or exactly one inline scheme.
 */
final class AuthenticationPolicy extends BaseObject
{
    /**
     * The name of the reusable authentication policy to use.
     */
    public string|null $use = null;

    public BasicAuthentication|null $basic = null;

    public BearerAuthentication|null $bearer = null;

    public DigestAuthentication|null $digest = null;

    public OAuth2Authentication|null $oauth2 = null;

    public OpenIdConnectAuthentication|null $oidc = null;
}

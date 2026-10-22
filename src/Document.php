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

/**
 * Documents the workflow.
 */
final class Document extends BaseObject
{
    public string|null $dsl = null;

    public string|null $namespace = null;

    public string|null $name = null;

    public string|null $version = null;

    public string|null $title = null;

    public string|null $summary = null;

    /**
     * @var array<string, mixed>|null
     */
    public array|null $tags = null;

    /**
     * @var array<string, mixed>|null
     */
    public array|null $metadata = null;
}

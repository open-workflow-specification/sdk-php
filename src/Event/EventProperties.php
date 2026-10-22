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

namespace Serverless\Workflow\Event;

use Serverless\Workflow\BaseObject;

/**
 * Describes the properties of an event. Additional CloudEvents attributes are preserved as additional properties.
 */
final class EventProperties extends BaseObject
{
    public string|null $id = null;

    public string|null $source = null;

    public string|null $type = null;

    public string|null $time = null;

    public string|null $subject = null;

    public string|null $datacontenttype = null;

    public string|null $dataschema = null;

    public mixed $data = null;
}

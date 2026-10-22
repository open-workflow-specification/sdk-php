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

use InvalidArgumentException;
use Serverless\Workflow\Serialization\Serializer;
use Symfony\Component\Yaml\Yaml;

/**
 * A workflow definition as described by the Open Workflow Specification DSL.
 */
final class Workflow extends BaseObject
{
    /**
     * The DSL version this SDK conforms to.
     */
    public const DSL_VERSION = '1.0.1';

    public Document|null $document = null;

    public Input|null $input = null;

    public Components|null $use = null;

    public TaskList|null $do = null;

    public string|Timeout|null $timeout = null;

    public Output|null $output = null;

    public Schedule|null $schedule = null;

    public function toJson(): string
    {
        return json_encode(
            Serializer::normalize($this, true),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            throw new InvalidArgumentException('A workflow definition must be a JSON object.');
        }

        return new self($data);
    }

    public function toYaml(): string
    {
        $yaml = Yaml::dump(
            Serializer::normalize($this, true),
            100,
            2,
            Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );

        // Start list items that hold a mapping on the dash line ("- name:"), as the specification examples do.
        return preg_replace('/^( *)-\n\1  (?=\S)/m', '$1- ', $yaml) ?? $yaml;
    }

    public static function fromYaml(string $yaml): self
    {
        $data = Yaml::parse($yaml);
        if (! is_array($data)) {
            throw new InvalidArgumentException('A workflow definition must be a YAML mapping.');
        }

        return new self($data);
    }

    /**
     * Validates the definition against the DSL schema.
     */
    public function validate(): void
    {
        WorkflowValidator::validate($this);
    }
}

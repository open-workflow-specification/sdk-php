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

use RuntimeException;

/**
 * Raised when a workflow definition does not comply with the DSL schema.
 */
final class WorkflowValidationException extends RuntimeException
{
    /**
     * @param array<string, list<string>> $errors validation messages keyed by JSON pointer
     */
    public function __construct(
        private readonly array $errors
    ) {
        parent::__construct(self::buildMessage($errors));
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<string, list<string>> $errors
     */
    private static function buildMessage(array $errors): string
    {
        $lines = [];
        foreach ($errors as $pointer => $messages) {
            foreach ($messages as $message) {
                $lines[] = sprintf('%s: %s', $pointer === '' ? '/' : $pointer, $message);
            }
        }

        return "The workflow definition is invalid:\n" . implode("\n", $lines);
    }
}

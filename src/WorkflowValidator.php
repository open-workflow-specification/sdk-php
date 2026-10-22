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
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use RuntimeException;
use Serverless\Workflow\Serialization\Serializer;

/**
 * Validates workflow definitions against the DSL JSON schema bundled with the SDK.
 */
final class WorkflowValidator
{
    public const SCHEMA_PATH = __DIR__ . '/../schema/workflow.json';

    private const MAX_ERRORS = 100;

    private static object|null $schema = null;

    /**
     * @param Workflow|mixed[] $workflow
     */
    public static function validate(Workflow|array $workflow): void
    {
        $errors = self::errors($workflow);
        if ($errors !== []) {
            throw new WorkflowValidationException($errors);
        }
    }

    /**
     * Returns the validation errors keyed by JSON pointer, or an empty array when the definition is valid.
     *
     * @param Workflow|mixed[] $workflow
     * @return array<string, list<string>>
     */
    public static function errors(Workflow|array $workflow): array
    {
        $decoded = json_decode(
            json_encode(self::normalize($workflow), JSON_THROW_ON_ERROR),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        $validator = new Validator();
        $validator->setMaxErrors(self::MAX_ERRORS);
        $result = $validator->validate($decoded, self::schema());
        $error = $result->error();
        if ($error === null) {
            return [];
        }

        $errors = [];
        foreach ((new ErrorFormatter())->format($error) as $pointer => $messages) {
            $errors[(string) $pointer] = array_values(array_map('strval', (array) $messages));
        }

        return $errors;
    }

    /**
     * Arrays are hydrated into the model first so that empty objects the model knows about (for example an empty
     * backoff definition) are encoded as objects rather than empty lists. Data the model cannot hydrate is
     * validated as is.
     *
     * @param Workflow|mixed[] $workflow
     */
    private static function normalize(Workflow|array $workflow): mixed
    {
        if (is_array($workflow)) {
            try {
                $workflow = new Workflow($workflow);
            } catch (InvalidArgumentException) {
                return $workflow;
            }
        }

        return Serializer::normalize($workflow, true);
    }

    private static function schema(): object
    {
        if (self::$schema === null) {
            $contents = file_get_contents(self::SCHEMA_PATH);
            if ($contents === false) {
                throw new RuntimeException(sprintf('Unable to read the workflow schema at %s.', self::SCHEMA_PATH));
            }

            $schema = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
            if (! is_object($schema)) {
                throw new RuntimeException('The workflow schema must be a JSON object.');
            }

            self::$schema = $schema;
        }

        return self::$schema;
    }
}

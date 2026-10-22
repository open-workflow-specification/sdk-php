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

use ReflectionClass;
use ReflectionProperty;
use Serverless\Workflow\Hydration\Hydratable;
use Serverless\Workflow\Hydration\Hydrator;
use Serverless\Workflow\Serialization\Serializer;

/**
 * Base class of every DSL model object.
 *
 * Public properties mirror the DSL properties. Unknown properties are preserved as additional properties so that a
 * definition can be parsed, serialized and validated without losing data.
 */
abstract class BaseObject implements Hydratable
{
    /**
     * @var array<string, mixed>
     */
    private array $additionalProperties = [];

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data = [])
    {
        $this->additionalProperties = Hydrator::hydrate($this, $data);
    }

    public static function fromArray(array $data): static
    {
        $class = static::resolveClass($data);

        return new $class($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    public function setAdditionalProperty(string $name, mixed $value): static
    {
        $this->additionalProperties[$name] = $value;

        return $this;
    }

    /**
     * Returns the non-null property values, in serialization order, followed by the additional properties.
     *
     * @return array<string, mixed>
     */
    public function properties(): array
    {
        $properties = [];
        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                continue;
            }

            $value = $reflectionProperty->getValue($this);
            if ($value !== null) {
                $properties[$reflectionProperty->getName()] = $value;
            }
        }

        return $properties + $this->additionalProperties;
    }

    public function toArray(): array
    {
        return Serializer::toArray($this);
    }

    /**
     * Returns the concrete class to instantiate for the given data. Polymorphic models override this.
     *
     * @param mixed[] $data
     * @return class-string<static>
     */
    protected static function resolveClass(array $data): string
    {
        return static::class;
    }
}

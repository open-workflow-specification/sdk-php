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

namespace Serverless\Workflow\Hydration;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Serverless\Workflow\NamedList;
use TypeError;

/**
 * Assigns DSL data to the public properties of a model object, instantiating nested models on the way.
 */
final class Hydrator
{
    /**
     * Hydrates the public properties of the target and returns the data that matched no property.
     *
     * @param mixed[] $data
     * @return array<string, mixed>
     */
    public static function hydrate(object $target, array $data): array
    {
        $reflectionClass = new ReflectionClass($target);
        $additional = [];

        foreach ($data as $key => $value) {
            $name = (string) $key;
            if (! $reflectionClass->hasProperty($name)) {
                $additional[$name] = $value;
                continue;
            }

            $property = $reflectionClass->getProperty($name);
            if (! $property->isPublic() || $property->isStatic()) {
                $additional[$name] = $value;
                continue;
            }

            try {
                $property->setValue($target, self::cast($property, $value));
            } catch (TypeError) {
                // Keep values the model cannot represent so they survive serialization and schema validation.
                $additional[$name] = $value;
            }
        }

        return $additional;
    }

    private static function cast(ReflectionProperty $reflectionProperty, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        foreach ($reflectionProperty->getAttributes() as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            if ($attribute instanceof ListOf || $attribute instanceof MapOf) {
                return is_array($value) ? self::castItems($attribute->class, $value) : $value;
            }

            if ($attribute instanceof NamedListOf) {
                if ($value instanceof NamedList) {
                    return $value;
                }

                return is_array($value) ? NamedList::of($attribute->class, $value) : $value;
            }
        }

        $type = $reflectionProperty->getType();
        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
        } elseif ($type instanceof ReflectionNamedType) {
            $types = [$type];
        } else {
            $types = [];
        }

        foreach ($types as $namedType) {
            if (! $namedType instanceof ReflectionNamedType || $namedType->isBuiltin()) {
                continue;
            }

            $class = $namedType->getName();
            if ($value instanceof $class) {
                return $value;
            }

            if (is_array($value) && is_subclass_of($class, Hydratable::class)) {
                return $class::fromArray($value);
            }
        }

        return $value;
    }

    /**
     * @param class-string<Hydratable> $class
     * @param mixed[] $items
     * @return mixed[]
     */
    private static function castItems(string $class, array $items): array
    {
        $result = [];
        foreach ($items as $key => $item) {
            $result[$key] = is_array($item) ? $class::fromArray($item) : $item;
        }

        return $result;
    }
}

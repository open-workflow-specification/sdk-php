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

namespace Serverless\Workflow\Serialization;

use ArrayObject;
use Serverless\Workflow\BaseObject;
use Serverless\Workflow\Hydration\ArrayConvertible;
use Serverless\Workflow\NamedList;

/**
 * Converts model objects into plain PHP structures for arrays, JSON and YAML.
 */
final class Serializer
{
    /**
     * Normalizes a value into arrays and scalars.
     *
     * When $preserveEmptyObjects is true, objects without properties become ArrayObject instances so that JSON and
     * YAML encoders emit "{}" instead of "[]". Otherwise they become empty arrays.
     */
    public static function normalize(mixed $value, bool $preserveEmptyObjects = false): mixed
    {
        if ($value instanceof BaseObject) {
            $properties = [];
            foreach ($value->properties() as $name => $propertyValue) {
                $properties[$name] = self::normalize($propertyValue, $preserveEmptyObjects);
            }

            return $preserveEmptyObjects && $properties === [] ? new ArrayObject() : $properties;
        }

        if ($value instanceof NamedList) {
            $items = [];
            foreach ($value->all() as $name => $item) {
                $items[] = [
                    $name => self::normalize($item, $preserveEmptyObjects),
                ];
            }

            return $items;
        }

        if ($value instanceof ArrayConvertible) {
            return self::normalize($value->toArray(), $preserveEmptyObjects);
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::normalize($item, $preserveEmptyObjects);
            }

            return $value;
        }

        return $value;
    }

    /**
     * @return mixed[]
     */
    public static function toArray(ArrayConvertible $arrayConvertible): array
    {
        $normalized = self::normalize($arrayConvertible);

        return is_array($normalized) ? $normalized : [];
    }
}

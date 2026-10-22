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

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Serverless\Workflow\Hydration\ArrayConvertible;
use Serverless\Workflow\Hydration\Hydratable;
use Serverless\Workflow\Serialization\Serializer;
use Traversable;

/**
 * An ordered collection of uniquely named items, serialized as a list of single-entry maps.
 *
 * @template T of Hydratable
 * @implements IteratorAggregate<string, T>
 */
class NamedList implements ArrayConvertible, IteratorAggregate, Countable
{
    /**
     * @var array<string, T>
     */
    private array $items = [];

    /**
     * Accepts either a name/item map or the DSL form, a list of single-entry maps. Items may be instances of the item
     * class or arrays to hydrate.
     *
     * @param class-string<T> $itemClass
     * @param mixed[] $items
     */
    public function __construct(
        private readonly string $itemClass,
        array $items = []
    ) {
        foreach ($items as $key => $value) {
            if (is_int($key)) {
                if (! is_array($value)) {
                    throw new InvalidArgumentException('A named list entry must be a single-entry map.');
                }

                foreach ($value as $name => $item) {
                    $this->add((string) $name, $item);
                }

                continue;
            }

            $this->add($key, $value);
        }
    }

    /**
     * @template U of Hydratable
     * @param class-string<U> $itemClass
     * @param mixed[] $items
     * @return self<U>
     */
    public static function of(string $itemClass, array $items = []): self
    {
        return new self($itemClass, $items);
    }

    /**
     * @return class-string<T>
     */
    public function getItemClass(): string
    {
        return $this->itemClass;
    }

    /**
     * @param T|mixed[] $item
     */
    public function add(string $name, mixed $item): static
    {
        if (is_array($item)) {
            $item = ($this->itemClass)::fromArray($item);
        }

        if (! $item instanceof $this->itemClass) {
            throw new InvalidArgumentException(sprintf(
                'Item "%s" must be an instance of %s or an array, %s given.',
                $name,
                $this->itemClass,
                get_debug_type($item)
            ));
        }

        $this->items[$name] = $item;

        return $this;
    }

    public function remove(string $name): static
    {
        unset($this->items[$name]);

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->items);
    }

    /**
     * @return T|null
     */
    public function get(string $name): ?Hydratable
    {
        return $this->items[$name] ?? null;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->items);
    }

    /**
     * @return array<string, T>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<string, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function toArray(): array
    {
        return Serializer::toArray($this);
    }
}

<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Collection;

/**
 * @template TKey
 * @template TValue
 * @implements CollectionInterface<TKey, TValue>
 */
abstract class AbstractCollection implements CollectionInterface
{
    private int $key;

    /** @var TKey[] $keys */
    private array $keys;

    /** @var TValue[] items */
    private array $items;

    public function __construct()
    {
        $this->key   = 0;
        $this->keys  = [];
        $this->items = [];
    }

    /**
     * @return TValue
     */
    public function current(): mixed
    {
        $key = $this->keys[$this->key];
        return $this->items[$key];
    }

    public function next(): void
    {
        $this->key++;
    }

    /**
     * @return TKey
     */
    public function key(): mixed
    {
        return $this->keys[$this->key];
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    /**
     * @param TKey $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param TKey $offset
     * @param TValue $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
        $this->keys[]         = $offset;
    }

    /**
     * @param TKey $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $key = \array_search($offset, $this->keys);
        if ($key !== false) {
            unset($this->items[$offset]);
            unset($this->keys[$key]);
        }
    }

    public function count(): int
    {
        return \count($this->items);
    }
}

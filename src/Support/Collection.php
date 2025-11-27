<?php
/*
* File:     Collection.php
* Category: Support
* Author:   M. Goldenbaum
* Created:  16.03.18 03:13
* Updated:  -
*
* Description:
*  Collection class replacing Illuminate\Support\Collection
*/

namespace Webklex\PHPIMAP\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Class Collection
 *
 * @package Webklex\PHPIMAP\Support
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate {

    /**
     * The items contained in the collection.
     *
     * @var array<TKey, TValue>
     */
    protected array $items = [];

    /**
     * Create a new collection.
     *
     * @param mixed $items
     */
    public function __construct(mixed $items = []) {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Create a new collection instance.
     *
     * @param mixed $items
     * @return static
     */
    public static function make(mixed $items = []): static {
        return new static($items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array<TKey, TValue>
     */
    public function all(): array {
        return $this->items;
    }

    /**
     * Get the values of a given key.
     *
     * @param string|array|int|null $value
     * @param string|null $key
     * @return static
     */
    public function pluck(string|array|int|null $value, ?string $key = null): static {
        $results = [];

        foreach ($this->items as $item) {
            $itemValue = data_get($item, $value);

            if ($key === null) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return new static($results);
    }

    /**
     * Run a map over each of the items.
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Run a filter over each of the items.
     *
     * @param callable|null $callback
     * @return static
     */
    public function filter(?callable $callback = null): static {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param string $key
     * @param mixed $operator
     * @param mixed $value
     * @return static
     */
    public function where(string $key, mixed $operator = null, mixed $value = null): static {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->filter(function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            switch ($operator) {
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
                default:
                    return $retrieved == $value;
            }
        });
    }

    /**
     * Get the first item from the collection.
     *
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public function first(?callable $callback = null, mixed $default = null): mixed {
        if ($callback === null) {
            if (empty($this->items)) {
                return $default;
            }

            return reset($this->items);
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get the last item from the collection.
     *
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public function last(?callable $callback = null, mixed $default = null): mixed {
        if ($callback === null) {
            if (empty($this->items)) {
                return $default;
            }

            $values = array_values($this->items);
            return $values[count($values) - 1] ?? $default;
        }

        return $this->filter($callback)->last(null, $default);
    }

    /**
     * Push an item onto the end of the collection.
     *
     * @param mixed ...$values
     * @return $this
     */
    public function push(mixed ...$values): static {
        foreach ($values as $value) {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Put an item in the collection by key.
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function put(mixed $key, mixed $value): static {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get(mixed $key, mixed $default = null): mixed {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param mixed $key
     * @return bool
     */
    public function has(mixed $key): bool {
        return array_key_exists($key, $this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int {
        return count($this->items);
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool {
        return empty($this->items);
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool {
        return !$this->isEmpty();
    }

    /**
     * Execute a callback over each item.
     *
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback): static {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys(): static {
        return new static(array_keys($this->items));
    }

    /**
     * Get the values of the collection items.
     *
     * @return static
     */
    public function values(): static {
        return new static(array_values($this->items));
    }

    /**
     * Merge the collection with the given items.
     *
     * @param mixed $items
     * @return static
     */
    public function merge(mixed $items): static {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse(): static {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Get a slice of items from the collection for a page.
     *
     * @param int $page
     * @param int|null $perPage
     * @return static
     */
    public function forPage(int $page, ?int $perPage = null): static {
        if ($perPage === null) {
            return $this;
        }
        $offset = max(0, ($page - 1) * $perPage);

        return new static(array_slice($this->items, $offset, $perPage, true));
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed {
        return $this->items[$offset] ?? null;
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void {
        unset($this->items[$offset]);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): Traversable {
        return new ArrayIterator($this->items);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array {
        return array_map(function ($value) {
            return $value instanceof self ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     * @return array
     */
    protected function getArrayableItems(mixed $items): array {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString(): string {
        return json_encode($this->toArray());
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param callable $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial = null): mixed {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param mixed $key
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    public function contains(mixed $key, mixed $operator = null, mixed $value = null): bool {
        if (func_num_args() === 1) {
            if (is_callable($key)) {
                foreach ($this->items as $k => $item) {
                    if ($key($item, $k)) {
                        return true;
                    }
                }
                return false;
            }

            return in_array($key, $this->items);
        }

        // Handle two-argument form: contains('key', 'value') means equality
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->contains(function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            switch ($operator) {
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
                default:
                    return $retrieved == $value;
            }
        });
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed
     */
    public function pop(): mixed {
        return array_pop($this->items);
    }

    /**
     * Chunk the collection into chunks of the given size.
     *
     * @param int $size
     * @return static
     */
    public function chunk(int $size): static {
        if ($size <= 0) {
            return new static;
        }

        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param int $limit
     * @return static
     */
    public function take(int $limit): static {
        if ($limit < 0) {
            return new static(array_slice($this->items, $limit, abs($limit)));
        }

        return new static(array_slice($this->items, 0, $limit));
    }

    /**
     * Sort through each item with a callback.
     *
     * @param callable|null $callback
     * @return static
     */
    public function sort(?callable $callback = null): static {
        $items = $this->items;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items);

        return new static($items);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param callable|string $callback
     * @param int $options
     * @param bool $descending
     * @return static
     */
    public function sortBy(callable|string $callback, int $options = SORT_REGULAR, bool $descending = false): static {
        $results = [];

        $callback = is_callable($callback)
            ? $callback
            : function ($item) use ($callback) {
                return data_get($item, $callback);
            };

        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options) : asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    /**
     * Collapse the collection of items into a single array.
     *
     * @return static
     */
    public function collapse(): static {
        $results = [];

        foreach ($this->items as $item) {
            if ($item instanceof self) {
                $item = $item->all();
            }

            if (is_array($item)) {
                $results = array_merge($results, $item);
            }
        }

        return new static($results);
    }

    /**
     * Flatten a multi-dimensional collection into a single level.
     *
     * @param int $depth
     * @return static
     */
    public function flatten(int $depth = INF): static {
        return new static(static::flattenArray($this->items, $depth));
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param array $array
     * @param int $depth
     * @return array
     */
    protected static function flattenArray(array $array, int $depth): array {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof self ? $item->all() : $item;

            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, static::flattenArray($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * Unique items in the collection.
     *
     * @param string|callable|null $key
     * @param bool $strict
     * @return static
     */
    public function unique(string|callable|null $key = null, bool $strict = false): static {
        if ($key === null) {
            return new static(array_unique($this->items, SORT_REGULAR));
        }

        $callback = is_callable($key)
            ? $key
            : function ($item) use ($key) {
                return data_get($item, $key);
            };

        $exists = [];

        return $this->filter(function ($item, $key) use ($callback, $strict, &$exists) {
            $id = $callback($item, $key);

            if (in_array($id, $exists, $strict)) {
                return false;
            }

            $exists[] = $id;

            return true;
        });
    }

    /**
     * Flip the items in the collection.
     *
     * @return static
     */
    public function flip(): static {
        return new static(array_flip($this->items));
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @param mixed ...$items
     * @return static
     */
    public function zip(mixed ...$items): static {
        $arrayableItems = array_map(function ($items) {
            return $this->getArrayableItems($items);
        }, $items);

        $params = array_merge([function (...$items) {
            return new static($items);
        }, $this->items], $arrayableItems);

        return new static(array_map(...$params));
    }
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param mixed $target
 * @param string|array|int|null $key
 * @param mixed $default
 * @return mixed
 */
if (!function_exists('data_get')) {
    function data_get(mixed $target, string|array|int|null $key, mixed $default = null): mixed {
        if ($key === null) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return $default;
                }
                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
                    return $default;
                }
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }
}

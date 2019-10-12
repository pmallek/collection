<?php


namespace Pmallek\Collection;

use ArrayAccess;
use ArrayIterator;

/**
 * Class Collection
 * @package DigitalAtlas\Collection
 */
class Collection implements ArrayAccess
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = $items;
    }

    /**
     * @param int $number
     * @param callable|null $callback
     *
     * @return Collection|static
     */
    public static function times($number, callable $callback = null)
    {
        if ($number < 1) {
            return new static;
        }

        if ($callback === null) {
            return new static(range(1, $number));
        }

        return (new static(range(1, $number)))->map($callback);
    }

    /**
     * @param array $items
     *
     * @return Collection
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @param callable|null $callable
     *
     * @return $this
     */
    public function filter(callable $callable = null)
    {
        return new static(array_filter($this->items, $callable));
    }

    /**
     * @param mixed $default
     *
     * @return mixed|null
     */
    public function first($default = null)
    {
        foreach ($this->items as $item) {
            return $item;
        }

        return is_callable($default) ? $default() : $default;
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function last($default = null)
    {
        foreach (array_reverse($this->items, true) as $item) {
            return $item;
        }

        return is_callable($default) ? $default() : $default;
    }

    /**
     * @return $this
     */
    public function flip()
    {
        return new static(array_flip($this->items));
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * @return $this
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function map(callable $callback)
    {
        $keys  = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function push($value)
    {
        $this->items[] = $value;

        return $this;
    }

    /**
     * @param callable $callback
     * @param null $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @param array|string $keys
     *
     * @return $this
     */
    public function forget($keys)
    {
        foreach ((array)$keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function reverse()
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return is_callable($default) ? $default() : $default;
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        foreach ($keys as $value) {
            if ( ! $this->offsetExists($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * @param int $offset
     * @param int $length
     *
     * @return $this
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * @param int $numberOfGroups
     *
     * @return $this
     */
    public function split($numberOfGroups)
    {
        if ($this->isEmpty()) {
            return new static;
        }
        $groups    = new static;
        $groupSize = floor($this->count() / $numberOfGroups);
        $remain    = $this->count() % $numberOfGroups;
        $start     = 0;
        for ($i = 0; $i < $numberOfGroups; $i++) {
            $size = $groupSize;
            if ($i < $remain) {
                $size++;
            }
            if ($size) {
                $groups->push(new static(array_slice($this->items, $start, $size)));
                $start += $size;
            }
        }

        return $groups;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function chunk($size)
    {
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
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param callable|null $callback
     *
     * @return $this
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;
        $callback
            ? uasort($items, $callback)
            : asort($items);

        return new static($items);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param int $options
     * @param bool $descending
     *
     * @return $this
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = $this->items;
        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * @param int $options
     *
     * @return $this
     */
    public function sortKeysDesc($options = SORT_REGULAR)
    {
        return $this->sortKeys($options, true);
    }

    /**
     * @param int $offset
     * @param int|null $length
     * @param array $replacement
     *
     * @return $this
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->items, $offset));
        }

        return new static(array_splice($this->items, $offset, $length, $replacement));
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function transform(callable $callback)
    {
        $this->items = $this->map($callback)->all();

        return $this;
    }

    /**
     * @return $this
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * @param int $size
     * @param mixed $value
     *
     * @return $this
     */
    public function pad($size, $value)
    {
        return new static(array_pad($this->items, $size, $value));
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param mixed $item
     *
     * @return $this
     */
    public function add($item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return Collection
     */
    public function toBase()
    {
        return new self($this);
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        if ($key === null) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

}
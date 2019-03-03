<?php

namespace Nerbiz\UrlEditor\Traits;

trait HasAssociativeArray
{
    /**
     * The array items
     * @var array
     */
    protected $items = [];

    /**
     * See whether a item exists (value can be null)
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Add an item
     * @param string $key
     * @param mixed  $value
     * @return self
     */
    public function add(string $key, $value)
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * Merge items with existing ones
     * @param array $items key => value pairs
     * @return self
     */
    public function mergeWith(array $items): self
    {
        $this->items = array_merge($this->items, $items);

        return $this;
    }

    /**
     * Remove an item
     * @param string $key
     * @return self
     */
    public function remove(string $key): self
    {
        if ($this->has($key)) {
            unset($this->items[$key]);
        }

        return $this;
    }

    /**
     * Remove an item by index
     * @param int $index
     * @return self
     */
    public function removeAt(int $index): self
    {
        $counter = -1;
        foreach ($this->items as $key => $value) {
            if (++$counter === $index) {
                unset($this->items[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Get an item
     * @param string $key     The name of the item
     * @param mixed  $default Fallback value, if the item doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }

        return $default;
    }
}

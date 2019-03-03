<?php

namespace Nerbiz\UrlEditor\Traits;

trait HasArray
{
    /**
     * The array items
     * @var array
     */
    protected $items = [];

    /**
     * See whether an item exists
     * @param string $item
     * @return bool
     */
    public function has(string $item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * Append an item
     * @param string $item
     * @return self
     */
    public function append(string $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Prepend an item
     * @param string $item
     * @return self
     */
    public function prepend(string $item): self
    {
        return $this->addAt(0, $item);
    }

    /**
     * Add an item at an array index
     * @param int    $index
     * @param string $item
     * @return self
     */
    public function addAt(int $index, string $item): self
    {
        array_splice($this->items, $index, 0, $item);

        return $this;
    }

    /**
     * Remove an item, either the first occurrence, or all occurrences
     * @param string $item
     * @param bool   $all
     * @return self
     */
    public function remove(string $item, bool $all = false): self
    {
        while (($key = array_search($item, $this->items)) !== false) {
            unset($this->items[$key]);

            // Stop looping if not all occurrences need to be removed
            if (!$all) {
                break;
            }
        }

        return $this;
    }

    /**
     * Remove an item at an array index
     * @param int $index
     * @return self
     */
    public function removeAt(int $index): self
    {
        if (array_key_exists($index, $this->items)) {
            unset($this->items[$index]);
        }

        return $this;
    }
}

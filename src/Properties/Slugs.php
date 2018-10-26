<?php

namespace Nerbiz\UrlEditor\Properties;

use InvalidArgumentException;

class Slugs
{
    /**
     * The slugs of a URL
     * @var array
     */
    protected $slugs = [];

    /**
     * @param string|array $slugs A string or array of slugs
     */
    public function __construct($slugs = null)
    {
        if ($slugs !== null) {
            if (is_string($slugs)) {
                $this->fromString($slugs);
            } elseif (is_array($slugs)) {
                $this->fromArray($slugs);
            } else {
                throw new InvalidArgumentException(sprintf(
                    "%s() expects parameter 'slugs' to be string or array, '%s' given",
                    __METHOD__,
                    is_object($slugs) ? get_class($slugs) : gettype($slugs)
                ));
            }
        }
    }

    /**
     * @param  string $slugs
     * @return self
     */
    public function fromString(string $slugs) : self
    {
        $this->slugs = explode('/', trim($slugs, '/'));

        return $this;
    }

    /**
     * @param  array $slugs
     * @return self
     */
    public function fromArray(array $slugs) : self
    {
        $this->slugs = $slugs;

        return $this;
    }

    /**
     * See whether a slug exists
     * @param  string $slug
     * @return bool
     */
    public function has(string $slug) : bool
    {
        return in_array($slug, $this->slugs, true);
    }

    /**
     * Add a slug
     * @param  string $slug
     * @return self
     */
    public function add(string $slug) : self
    {
        $this->slugs[] = $slug;

        return $this;
    }

    /**
     * Add a slug at an array index
     * @param  int    $index
     * @param  string $slug
     * @return self
     */
    public function addAt(int $index, string $slug) : self
    {
        array_splice($this->slugs, $index, 0, $slug);

        return $this;
    }

    /**
     * Merge slugs with existing ones
     * @param  array $slugs
     * @return self
     */
    public function mergeWith(array $slugs) : self
    {
        $this->slugs = array_merge($this->slugs, $slugs);

        return $this;
    }

    /**
     * Remove a slug, either the first occurence, or all occurences
     * @param  string $slug
     * @param  bool   $all
     * @return self
     */
    public function remove(string $slug, bool $all = false) : self
    {
        while (($key = array_search($slug, $this->slugs)) !== false) {
            unset($a[$key]);

            // Keep looping, if all occurences need to be removed
            if (! $all) {
                break;
            }
        }

        return $this;
    }

    /**
     * Remove a slug at an array index
     * @param  int $index
     * @return self
     */
    public function removeAt(int $index) : self
    {
        if (array_key_exists($index, $this->slugs)) {
            unset($this->slugs[$index]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function get() : array
    {
        return $this->slugs;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->slugs;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return implode('/', $this->slugs);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
}

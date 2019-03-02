<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidSlugsException;

class Slugs implements Stringable, Arrayable, Jsonable
{
    /**
     * The slugs of a URL
     * @var array
     */
    protected $slugs = [];

    /**
     * @param string|array|null $slugs A string or array of slugs
     * @throws InvalidSlugsException
     */
    public function __construct($slugs = null)
    {
        if ($slugs !== null) {
            if (is_string($slugs)) {
                $this->fromString($slugs);
            } elseif (is_array($slugs)) {
                $this->fromArray($slugs);
            } else {
                throw new InvalidSlugsException(sprintf(
                    "%s() expects parameter 'slugs' to be string or array, '%s' given",
                    __METHOD__,
                    is_object($slugs) ? get_class($slugs) : gettype($slugs)
                ));
            }
        }
    }

    /**
     * See whether a slug exists
     * @param string $slug
     * @return bool
     */
    public function has(string $slug): bool
    {
        return in_array($slug, $this->slugs, true);
    }

    /**
     * Add a slug
     * @param string $slug
     * @return self
     */
    public function add(string $slug): self
    {
        $this->slugs[] = $slug;

        return $this;
    }

    /**
     * Add a slug at an array index
     * @param int    $index
     * @param string $slug
     * @return self
     */
    public function addAt(int $index, string $slug): self
    {
        array_splice($this->slugs, $index, 0, $slug);

        return $this;
    }

    /**
     * Merge slugs with existing ones
     * @param array $slugs
     * @return self
     */
    public function mergeWith(array $slugs): self
    {
        $this->slugs = array_merge($this->slugs, $slugs);

        return $this;
    }

    /**
     * Remove a slug, either the first occurence, or all occurences
     * @param string $slug
     * @param bool   $all
     * @return self
     */
    public function remove(string $slug, bool $all = false): self
    {
        while (($key = array_search($slug, $this->slugs)) !== false) {
            unset($this->slugs[$key]);

            // Keep looping, if all occurences need to be removed
            if (!$all) {
                break;
            }
        }

        return $this;
    }

    /**
     * Remove a slug at an array index
     * @param int $index
     * @return self
     */
    public function removeAt(int $index): self
    {
        if (array_key_exists($index, $this->slugs)) {
            unset($this->slugs[$index]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $slugs): self
    {
        $this->slugs = array_values(array_filter(explode('/', $slugs)));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return implode('/', $this->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function fromArray(array $slugs): self
    {
        $this->slugs = array_values($slugs);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_values($this->slugs);
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

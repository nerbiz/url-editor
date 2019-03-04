<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;
use Nerbiz\UrlEditor\Exceptions\InvalidSlugsException;
use Nerbiz\UrlEditor\Traits\HasArray;

class Slugs implements Stringable, Arrayable, Jsonable
{
    use HasArray;

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
                    "%s() expects a string or array, '%s' given",
                    __METHOD__,
                    is_object($slugs) ? get_class($slugs) : gettype($slugs)
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $slugs): self
    {
        // Trim dots and spaces
        $slugs = trim($slugs, '. ');
        $this->items = $this->fromArray(explode('/', $slugs));

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
        $this->items = array_values(array_filter($slugs));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function fromJson(string $json): self
    {
        $decoded = json_decode($json, true);
        if ($decoded === null) {
            throw new InvalidJsonException(sprintf(
                "%s() expects valid JSON, error: '%s', '%s' given",
                __METHOD__,
                json_last_error_msg(),
                $json
            ));
        }

        return $this->fromArray($decoded);
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

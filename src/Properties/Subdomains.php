<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;
use Nerbiz\UrlEditor\Exceptions\InvalidSubdomainsException;
use Nerbiz\UrlEditor\Traits\HasArray;

class Subdomains implements Stringable, Arrayable, Jsonable
{
    use HasArray;

    /**
     * @param string|array|null $subdomains A string or array of subdomains
     * @throws InvalidSubdomainsException
     */
    public function __construct($subdomains = null)
    {
        if ($subdomains !== null) {
            if (is_string($subdomains)) {
                $this->fromString($subdomains);
            } elseif (is_array($subdomains)) {
                $this->fromArray($subdomains);
            } else {
                throw new InvalidSubdomainsException(sprintf(
                    "%s() expects a string or array, '%s' given",
                    __METHOD__,
                    is_object($subdomains) ? get_class($subdomains) : gettype($subdomains)
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $subdomains): self
    {
        return $this->fromArray(explode('.', $subdomains));
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return implode('.', $this->items);
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
    public function fromArray(array $subdomains): self
    {
        $this->items = array_values(array_filter(array_map(function ($item) {
            // Trim dots and spaces
            return trim($item, '. ');
        }, $subdomains)));

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

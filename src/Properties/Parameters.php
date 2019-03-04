<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;
use Nerbiz\UrlEditor\Exceptions\InvalidParametersException;
use Nerbiz\UrlEditor\Traits\HasAssociativeArray;

class Parameters implements Stringable, Arrayable, Jsonable
{
    use HasAssociativeArray;

    /**
     * @param string|array|null $parameters A string or array of parameters
     * @throws InvalidParametersException
     */
    public function __construct($parameters = null)
    {
        if ($parameters !== null) {
            if (is_string($parameters)) {
                $this->fromString($parameters);
            } elseif (is_array($parameters)) {
                $this->fromArray($parameters);
            } else {
                throw new InvalidParametersException(sprintf(
                    "%s() expects a string or array, '%s' given",
                    __METHOD__,
                    is_object($parameters) ? get_class($parameters) : gettype($parameters)
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $parameters): self
    {
        // Trim question marks, ampersands and spaces
        $parameters = trim($parameters, '?& ');

        // Make a list of parameters
        $parts = array_filter(explode('&', $parameters));

        // Create a parameters array
        $items = [];
        if (count($parts) > 0) {
            foreach ($parts as $part) {
                // If there is no equals sign, the value is null
                if (mb_strpos($part, '=') === false) {
                    $items[$part] = null;
                } else {
                    // Get the parameter name and value (decoded)
                    list($key, $value) = explode('=', $part);
                    $value = trim(urldecode($value));

                    // An empty string means null
                    if ($value === '') {
                        $value = null;
                    }

                    $items[$key] = $value;
                }
            }
        }

        $this->items = $this->fromArray($items);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        $parameters = [];
        foreach ($this->toArray() as $key => $value) {
            // http_build_query() skips nulls, so make it empty strings
            if ($value === null) {
                $value = '';
            }

            $parameters[$key] = $value;
        }

        return http_build_query($parameters);
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
    public function fromArray(array $parameters): self
    {
        $this->items = $parameters;

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

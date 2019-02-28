<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;

class Parameters implements Stringable, Arrayable, Jsonable
{
    /**
     * The parameters of a URL
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string|array|null $parameters A string or array of parameters
     * @throws \InvalidArgumentException
     */
    public function __construct($parameters = null)
    {
        if ($parameters !== null) {
            if (is_string($parameters)) {
                $this->fromString($parameters);
            } elseif (is_array($parameters)) {
                $this->fromArray($parameters);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    "%s() expects parameter 'parameters' to be string or array, '%s' given",
                    __METHOD__,
                    is_object($parameters) ? get_class($parameters) : gettype($parameters)
                ));
            }
        }
    }

    /**
     * See whether a parameter exists (value can be null)
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Add a parameter
     * @param string $key
     * @param mixed  $value
     * @return self
     */
    public function add(string $key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Merge parameters with existing ones
     * @param array $parameters key => value pairs
     * @return self
     */
    public function mergeWith(array $parameters): self
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Remove a parameter
     * @param string $key
     * @return self
     */
    public function remove(string $key): self
    {
        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }

        return $this;
    }

    /**
     * Remove a parameter by index
     * @param int $index
     * @return self
     */
    public function removeAt(int $index): self
    {
        $counter = -1;
        foreach ($this->parameters as $key => $value) {
            if (++$counter === $index) {
                unset($this->parameters[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Get 1 or all parameters
     * @param string $key     The name of the parameter
     * @param mixed  $default Fallback value, if the parameter doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $parameters): self
    {
        // Remove a leading question mark
        if (substr($parameters, 0, 1) === '?') {
            $parameters = substr($parameters, 1);
        }

        $this->parameters = [];
        $parts = array_filter(explode('&', $parameters));

        if (count($parts) > 0) {
            foreach ($parts as $part) {
                // If there is no equals sign, the value is null
                if (mb_strpos($part, '=') === false) {
                    $this->parameters[$part] = null;
                } else {
                    // Get the parameter name and value (decoded)
                    list($key, $value) = explode('=', $part);
                    $value = trim(urldecode($value));

                    // An empty string means null
                    if ($value === '') {
                        $value = null;
                    }

                    $this->parameters[$key] = $value;
                }
            }
        }

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
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

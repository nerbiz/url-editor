<?php

namespace Nerbiz\UrlEditor\Properties;

use InvalidArgumentException;

class Parameters
{
    /**
     * The parameters of a URL
     * @var array
     */
    protected $parameters = [];

    /**
     * @param string|array $parameters A string or array of parameters
     */
    public function __construct($parameters = null)
    {
        if ($parameters !== null) {
            if (is_string($parameters)) {
                $this->fromString($parameters);
            } elseif (is_array($parameters)) {
                $this->fromArray($parameters);
            } else {
                throw new InvalidArgumentException(sprintf(
                    "%s() expects parameter 'parameters' to be string or array, '%s' given",
                    __METHOD__,
                    is_object($parameters) ? get_class($parameters) : gettype($parameters)
                ));
            }
        }
    }

    /**
     * @param  string $parameters
     * @return self
     */
    public function fromString(string $parameters) : self
    {
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
                    $value = urldecode($value);

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
     * @param  array $parameters
     * @return self
     */
    public function fromArray(array $parameters) : self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * See whether a parameter exists (value can be null)
     * @param  string $key
     * @return bool
     */
    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Add a parameter
     * @param  string $key
     * @param  mixed $value
     * @return self
     */
    public function add(string $key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Merge parameters with existing ones
     * @param  array $parameters key => value pairs
     * @return self
     */
    public function mergeWith(array $parameters) : self
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Remove a parameter
     * @param  string $key
     * @return self
     */
    public function remove(string $key) : self
    {
        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }

        return $this;
    }

    /**
     * Remove a parameter by index
     * @param  int $index
     * @return self
     */
    public function removeAt(int $index) : self
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
     * @param  string|null  $key     The name of the parameter
     * @param  mixed        $default Fallback value, if the parameter doesn't exist
     * @return string|mixed
     */
    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->parameters;
        }

        if ($this->has($key)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->get();
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        $parameters = [];
        foreach ($this->parameters as $key => $value) {
            $parameters[] = $key . '=' . $this->get($key);
        }

        return implode('&', $parameters);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
}

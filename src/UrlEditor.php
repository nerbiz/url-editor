<?php

namespace Nerbiz;

class UrlEditor
{
    /**
     * The URL to work with
     * @var string
     */
    protected $url;

    /**
     * All the URL parts
     * @var array
     */
    protected $urlParts;

    /**
     * The query parameters of the URL
     * @var array
     */
    protected $queryParameters = [];

    /**
     * The fragment of the URL
     * @var string|null
     */
    protected $fragment = null;

    /**
     * @param string|null $url The URL to work with, current URL if omitted
     */
    public function __construct(string $url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        } else {
            $this->url = sprintf(
                'http%s://%s%s',
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 's' : '',
                rtrim($_SERVER['HTTP_HOST'], '/'),
                '/' . ltrim($_SERVER['REQUEST_URI'], '/')
            );
        }

        $this->urlParts = parse_url($this->url);
        $this->setInitialParametersArray();
        $this->fragment = $this->urlParts['fragment'] ?? null;
    }

    /**
     * Set the initial query parameter array
     * @return void
     */
    protected function setInitialParametersArray() : void
    {
        if (isset($this->urlParts['query'])) {
            // Add the parameters, if there are any
            $parts = explode('&', $this->urlParts['query']);
            if (count($parts) > 0) {
                foreach ($parts as $part) {
                    // If there is no equals sign, the value is null
                    if (mb_strpos($part, '=') === false) {
                        $this->queryParameters[$part] = null;
                    } else {
                        // Get the parameter name and value (decoded)
                        list($key, $value) = explode('=', $part);
                        $value = urldecode($value);

                        // An empty string means null
                        if ($value === '') {
                            $value = null;
                        }

                        $this->queryParameters[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Get the URL parts
     * @return array
     */
    public function getParts() : array
    {
        return $this->urlParts;
    }

    /**
     * Get the scheme of the URL
     * @return string
     */
    public function getScheme() : string
    {
        return $this->urlParts['scheme'];
    }

    /**
     * Get the host of the URL
     * @return string
     */
    public function getHost() : string
    {
        return $this->urlParts['host'];
    }

    /**
     * Get the path of the URL
     * @return string
     */
    public function getPath() : string
    {
        return $this->urlParts['path'];
    }

    /**
     * Get the paths of the URL as an array
     * @return array
     */
    public function getPathArray() : array
    {
        return explode('/', trim($this->urlParts['path'], '/'));
    }

    /**
     * Get the query parameters of the URL
     * @return string|null
     */
    public function getParameters() : ?string
    {
        return $this->urlParts['query'] ?? null;
    }

    /**
     * Get the query parameters as an array
     * @return array
     */
    public function getParametersArray() : array
    {
        return $this->queryParameters;
    }

    /**
     * See whether a parameter exists in the URL (even if the value is null)
     * @param  string  $key
     * @return boolean
     */
    public function hasParameter($key) : bool
    {
        return array_key_exists($key, $this->queryParameters);
    }

    /**
     * Get a query parameter
     * @param  string $key     The name of the parameter
     * @param  mixed  $default Fallback value, if the parameter doesn't exist
     * @return string|null
     */
    public function getParameter(string $key, $default = null) : ?string
    {
        if ($this->hasParameter($key)) {
            return $this->queryParameters[$key];
        }

        return $default;
    }

    /**
     * Add a query parameter
     * @param  string $key
     * @param  mixed  $value
     * @return self
     */
    public function addParameter(string $key, $value) : self
    {
        $this->queryParameters[$key] = $value;

        return $this;
    }

    /**
     * Add/replace one or more query parameters
     * @param  array $pairs key => value pairs
     * @return self
     */
    public function mergeParameters(array $pairs) : self
    {
        $this->queryParameters = array_merge($this->queryParameters, $pairs);

        return $this;
    }

    /**
     * Remove a query parameter
     * @param  string $key
     * @return self
     */
    public function removeParameter(string $key) : self
    {
        if ($this->hasParameter($key)) {
            unset($this->queryParameters[$key]);
        }

        return $this;
    }

    /**
     * Get the fragment of the URL
     * @return string|null
     */
    public function getFragment() : ?string
    {
        return $this->fragment;
    }

    /**
     * Set the fragment of the URL
     * @param  string $fragment
     * @return self
     */
    public function setFragment(string $fragment) : self
    {
        $this->fragment = urlencode($fragment);

        return $this;
    }

    /**
     * Alias of getFragment()
     * @return string|null
     */
    public function getHash() : ?string
    {
        return $this->getFragment();
    }

    /**
     * Alias of setFragment()
     * @param  string $hash
     * @return self
     */
    public function setHash(string $hash) : self
    {
        return $this->setFragment($hash);
    }

    /**
     * Get the base URL
     * @return string
     */
    public function getBase()
    {
        return sprintf(
            '%s://%s',
            $this->urlParts['scheme'],
            $this->urlParts['host']
        );
    }

    /**
     * Get the full URL
     * @return string
     */
    public function getFull() : string
    {
        // Prepare the query array: nulls become empty strings
        $queryParameters = [];
        foreach ($this->queryParameters as $key => &$value) {
            // http_build_query() skips nulls, so make it empty strings
            if ($value === null) {
                $value = '';
            }

            $queryParameters[$key] = $value;
        }

        // Return a fully re-constructed URL
        return sprintf(
            '%s%s%s%s',
            $this->getBase(),
            $this->getPath(),
            (count($queryParameters) > 0)
                ? '?' . http_build_query($queryParameters)
                : '',
            ($this->getFragment() !== null)
                ? '#' . $this->getFragment()
                : ''
        );
    }

    /**
     * Redirect to the full URL
     * @param  int  $statusCode The HTTP status code of the redirect (default = 303)
     * @return void
     */
    public function redirect($statusCode = 303)
    {
        header(
            sprintf('Location: %s', $this->getFull()),
            true,
            $statusCode
        );

        exit;
    }
}

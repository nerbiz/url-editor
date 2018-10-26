<?php

namespace Nerbiz\UrlEditor;

use Nerbiz\UrlEditor\Properties\Fragment;
use Nerbiz\UrlEditor\Properties\Parameters;
use Nerbiz\UrlEditor\Properties\Slugs;

use Exception;

class UrlEditor
{
    /**
     * @var Fragment
     */
    protected $fragment;

    /**
     * @var Parameters
     */
    protected $parameters;

    /**
     * @var Slugs
     */
    protected $slugs;

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
     * Whether the URL is secure (https) or not (http)
     * @var bool
     */
    protected $isSecure;

    /**
     * @param string|null $url The URL to work with, current URL if omitted
     */
    public function __construct(string $url = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        } else {
            $this->url = sprintf(
                'http%s://%s%s',
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 's' : '',
                rtrim($_SERVER['HTTP_HOST'], '/'),
                '/' . ltrim($_SERVER['REQUEST_URI'], '/')
            );
        }

        $this->urlParts = parse_url($this->url);
        $this->setIsSecure(mb_substr($this->urlParts['scheme'], 0, 5) === 'https');

        $this->parameters = new Parameters($this->urlParts['query'] ?? null);
        $this->slugs = new Slugs($this->urlParts['path'] ?? null);
        $this->fragment = new Fragment($this->urlParts['fragment'] ?? null);
    }

    /**
     * @param  string $url
     * @return self
     */
    public function setUrl(string $url) : self
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \Exception(sprintf(
                "%s(): invalid URL provided",
                __METHOD__
            ));
        }

        $this->url = $url;

        return $this;
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
     * @return bool
     */
    public function isSecure() : bool
    {
        return $this->isSecure;
    }

    /**
     * @param bool  $secure
     */
    public function setIsSecure(bool $secure) : self
    {
        $this->isSecure = $secure;

        return $this;
    }

    /**
     * Get the base URL
     * @return string
     */
    public function getBase()
    {
        return sprintf(
            'http%s://%s',
            $this->isSecure() ? 's' : '',
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
        foreach ($this->getParameters()->toArray() as $key => &$value) {
            // http_build_query() skips nulls, so make it empty strings
            if ($value === null) {
                $value = '';
            }

            $queryParameters[$key] = $value;
        }

        // The fragment part of the URL
        $fragment = $this->getFragment()->toString();

        // Return a fully re-constructed URL
        return sprintf(
            '%s/%s%s%s',
            rtrim($this->getBase(), '/'),
            $this->getSlugs()->toString(),
            (count($queryParameters) > 0)
                ? '?' . http_build_query($queryParameters)
                : '',
            ($fragment !== '')
                ? '#' . $fragment
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

    /**
     * @return Fragment
     */
    public function getFragment() : Fragment
    {
        return $this->fragment;
    }

    /**
     * Alias of getFragment()
     * @return Fragment
     */
    public function getAnchor() : Fragment
    {
        return $this->getFragment();
    }

    /**
     * @return Parameters
     */
    public function getParameters() : Parameters
    {
        return $this->parameters;
    }

    /**
     * @return Slugs
     */
    public function getSlugs() : Slugs
    {
        return $this->slugs;
    }
}

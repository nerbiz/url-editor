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
     * @param string|null $url The URL to work with, or current URL if null
     * @throws \InvalidArgumentException
     */
    public function __construct(?string $url = null)
    {
        // Set a given URL, or use the current
        if ($url !== null) {
            $this->setUrl($url);
        } else {
            $this->setUrl(sprintf(
                'http%s://%s%s',
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 's' : '',
                rtrim($_SERVER['HTTP_HOST'], '/'),
                '/' . ltrim($_SERVER['REQUEST_URI'], '/')
            ));
        }

        $this->urlParts = parse_url($this->url);
        $this->setIsSecure(mb_substr($this->urlParts['scheme'], 0, 5) === 'https');

        $this->parameters = new Parameters($this->urlParts['query'] ?? null);
        $this->slugs = new Slugs($this->urlParts['path'] ?? null);
        $this->fragment = new Fragment($this->urlParts['fragment'] ?? null);
    }

    /**
     * @param string $url
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setUrl(string $url): self
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException(sprintf(
                "%s(): invalid URL provided: '%s'",
                __METHOD__,
                is_object($url) ? get_class($url) : $url
            ));
        }

        $this->url = $url;

        return $this;
    }

    /**
     * Get the URL parts
     * @return array
     */
    public function getParts(): array
    {
        return $this->urlParts;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->isSecure;
    }

    /**
     * @param bool $secure
     * @return self
     */
    public function setIsSecure(bool $secure): self
    {
        $this->isSecure = $secure;

        return $this;
    }

    /**
     * Get the base URL
     * @return string
     */
    public function getBase(): string
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
    public function getFull(): string
    {
        $parameters = $this->getParameters()->toString();
        $fragment = $this->getFragment()->toString();

        // Return a fully re-constructed URL
        return sprintf(
            '%s/%s%s%s',
            rtrim($this->getBase(), '/'),
            $this->getSlugs()->toString(),
            ($parameters !== '')
                ? '?' . $parameters
                : '',
            ($fragment !== '')
                ? '#' . $fragment
                : ''
        );
    }

    /**
     * Redirect to the full URL
     * @param int $statusCode The HTTP status code of the redirect
     * @return void
     */
    public function redirect(int $statusCode = 302): void
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
    public function getFragment(): Fragment
    {
        return $this->fragment;
    }

    /**
     * Alias of getFragment()
     * @return Fragment
     */
    public function getAnchor(): Fragment
    {
        return $this->getFragment();
    }

    /**
     * @return Parameters
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    /**
     * @return Slugs
     */
    public function getSlugs(): Slugs
    {
        return $this->slugs;
    }
}

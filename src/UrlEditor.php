<?php

namespace Nerbiz\UrlEditor;

use Nerbiz\UrlEditor\Exceptions\InvalidUrlException;
use Nerbiz\UrlEditor\Properties\Fragment;
use Nerbiz\UrlEditor\Properties\Host;
use Nerbiz\UrlEditor\Properties\Parameters;
use Nerbiz\UrlEditor\Properties\Slugs;

class UrlEditor
{
    /**
     * @var Host
     */
    protected $host;

    /**
     * @var Slugs
     */
    protected $slugs;

    /**
     * @var Parameters
     */
    protected $parameters;

    /**
     * @var Fragment
     */
    protected $fragment;

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
     * @throws InvalidUrlException
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

        $this->host = new Host($this->urlParts['host']);
        $this->slugs = new Slugs($this->urlParts['path'] ?? null);
        $this->parameters = new Parameters($this->urlParts['query'] ?? null);
        $this->fragment = new Fragment($this->urlParts['fragment'] ?? null);
    }

    /**
     * @param string $url
     * @return self
     * @throws InvalidUrlException
     */
    public function setUrl(string $url): self
    {
        $this->checkUrl($url);
        $this->url = $url;

        return $this;
    }

    /**
     * Check the validity of a URL
     * @param string $url
     * @return bool
     * @throws InvalidUrlException
     */
    public function checkUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidUrlException(sprintf(
                "%s(): invalid URL: '%s'",
                __METHOD__,
                is_object($url) ? get_class($url) : $url
            ));
        }

        return true;
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
     * Get the full URL
     * @return string
     */
    public function getFull(): string
    {
        $slugs = $this->getSlugs()->toString();
        $parameters = $this->getParameters()->toString();
        $fragment = $this->getFragment()->toString();

        // Construct the full URL
        $fullUrl = sprintf(
            'http%s://%s%s%s%s',
            $this->isSecure() ? 's' : '',
            $this->getHost()->toString(),
            ($slugs !== '')
                ? '/' . $slugs
                : '',
            ($parameters !== '')
                ? '?' . $parameters
                : '',
            ($fragment !== '')
                ? '#' . $fragment
                : ''
        );

        // See if the URL is valid
        $this->checkUrl($fullUrl);
        return $fullUrl;
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
     * @return Host
     */
    public function getHost(): Host
    {
        return $this->host;
    }

    /**
     * @return Slugs
     */
    public function getSlugs(): Slugs
    {
        return $this->slugs;
    }

    /**
     * @return Parameters
     */
    public function getParameters(): Parameters
    {
        return $this->parameters;
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
}

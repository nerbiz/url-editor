<?php

namespace Nerbiz\UrlEditor;

use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidUrlException;
use Nerbiz\UrlEditor\Properties\Fragment;
use Nerbiz\UrlEditor\Properties\Host;
use Nerbiz\UrlEditor\Properties\HttpAuth;
use Nerbiz\UrlEditor\Properties\Parameters;
use Nerbiz\UrlEditor\Properties\Port;
use Nerbiz\UrlEditor\Properties\Slugs;

class UrlEditor implements Stringable
{
    /**
     * @var HttpAuth
     */
    protected $httpAuth;

    /**
     * @var Host
     */
    protected $host;

    /**
     * @var Port
     */
    protected $port;

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
            $this->fromString($url);
        } else {
            $this->fromString(sprintf(
                'http%s://%s%s',
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 's' : '',
                rtrim($_SERVER['HTTP_HOST'], '/'),
                '/' . ltrim($_SERVER['REQUEST_URI'], '/')
            ));
        }
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
     * Get the base of the URL
     * @return string
     */
    public function getBase(): string
    {
        $httpAuth = $this->getHttpAuth()->toString();
        $port = $this->getPort()->toString();

        $baseUrl = sprintf(
            'http%s://%s%s%s',
            $this->isSecure() ? 's' : '',
            ($httpAuth !== '')
                ? $httpAuth . '@'
                : '',
            $this->getHost()->toString(),
            ($port !== '')
                ? ':' . $port
                : ''
        );

        // See if the URL is valid
        $this->checkUrl($baseUrl);
        return $baseUrl;
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
            '%s%s%s%s',
            $this->getBase(),
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
     * @return HttpAuth
     */
    public function getHttpAuth(): HttpAuth
    {
        return $this->httpAuth;
    }

    /**
     * @return Host
     */
    public function getHost(): Host
    {
        return $this->host;
    }

    /**
     * @return Port
     */
    public function getPort(): Port
    {
        return $this->port;
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
     * @see UrlEditor::getFragment()
     */
    public function getAnchor(): Fragment
    {
        return $this->getFragment();
    }

    /**
     * {@inheritdoc}
     * @throws InvalidUrlException
     */
    public function fromString(string $url): self
    {
        $this->checkUrl($url);
        $this->url = $url;

        // Get the parts of the URL
        $urlParts = parse_url($this->url);

        // Set whether the URL is secure
        $this->setIsSecure(mb_substr($urlParts['scheme'], 0, 5) === 'https');

        // Create or update the HttpAuth object
        if ($this->httpAuth === null) {
            $this->httpAuth = new HttpAuth(
                $urlParts['user'] ?? null,
                $urlParts['pass'] ?? null
            );
        } else {
            $this->httpAuth->fromArray([
                'username' => $urlParts['user'] ?? null,
                'password' => $urlParts['pass'] ?? null,
            ]);
        }

        // Create or update the Host object
        if ($this->host === null) {
            $this->host = new Host($urlParts['host']);
        } else {
            $this->host->fromString($urlParts['host']);
        }

        // Create or update the Port object
        $implicitPort = ($this->isSecure()) ? 443 : 80;
        if ($this->port === null) {
            $this->port = new Port($urlParts['port'] ?? $implicitPort);
        } else {
            $this->port->fromInt($urlParts['port'] ?? $implicitPort);
        }

        // Create or update the Slugs object
        if ($this->slugs === null) {
            $this->slugs = new Slugs($urlParts['path'] ?? null);
        } else {
            $this->slugs->fromString($urlParts['path'] ?? null);
        }

        // Create or update the Parameters object
        if ($this->parameters === null) {
            $this->parameters = new Parameters($urlParts['query'] ?? null);
        } else {
            $this->parameters->fromString($urlParts['query'] ?? null);
        }

        // Create or update the Fragment object
        if ($this->fragment === null) {
            $this->fragment = new Fragment($urlParts['fragment'] ?? null);
        } else {
            $this->fragment->fromString($urlParts['fragment'] ?? null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->getFull();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

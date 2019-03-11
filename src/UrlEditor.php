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
use Nerbiz\UrlEditor\Properties\Subdomains;
use Nerbiz\UrlEditor\Properties\Tld;

class UrlEditor implements Stringable
{
    /**
     * @var HttpAuth
     */
    protected $httpAuth;

    /**
     * @var Subdomains
     */
    protected $subdomains;

    /**
     * @var Tld
     */
    protected $tld;

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
     * The original URL this object was constructed with
     * @var string
     */
    protected $originalUrl;

    /**
     * The parts of the URL (result of parse_url())
     * @var array
     */
    protected $urlParts;

    /**
     * The domain name without subdomains and TLD
     * Example: 'www.example.com' -> 'example'
     * @var string
     */
    protected $domainBase;

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
        $subdomains = $this->getSubdomains()->toString();
        $port = $this->getPort()->toString();

        $baseUrl = sprintf(
            'http%s://%s%s%s.%s%s',
            $this->isSecure() ? 's' : '',
            ($httpAuth !== '')
                ? $httpAuth . '@'
                : '',
            ($subdomains !== '')
                ? $subdomains . '.'
                : '',
            $this->domainBase,
            $this->getTld()->toString(),
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
     * {@inheritdoc}
     * @throws InvalidUrlException
     */
    public function fromString(string $url): self
    {
        $this->checkUrl($url);
        $this->originalUrl = $url;

        // Get the parts of the URL
        $this->urlParts = parse_url($this->originalUrl);

        // Set whether the URL is secure
        $this->setIsSecure(mb_substr($this->urlParts['scheme'], 0, 5) === 'https');

        // Create or update the HttpAuth object
        if ($this->httpAuth === null) {
            $this->httpAuth = new HttpAuth(
                $this->urlParts['user'] ?? null,
                $this->urlParts['pass'] ?? null
            );
        } else {
            $this->httpAuth->fromArray([
                'username' => $this->urlParts['user'] ?? null,
                'password' => $this->urlParts['pass'] ?? null,
            ]);
        }

        // Create or update the Tld object
        if ($this->tld === null) {
            $this->tld = new Tld($this->urlParts['host']);
        } else {
            $this->tld->fromHost($this->urlParts['host']);
        }

        // Get the domain name without the TLD and split it by '.'
        $tld = $this->getTld()->toString();
        $hostWithoutTld = trim(mb_substr($this->urlParts['host'], 0, (0 - strlen($tld))), '.');
        $parts = explode('.', $hostWithoutTld);

        // The last entry is the domain base
        // The remaining parts (if any) are the subdomains
        $this->domainBase = array_pop($parts);

        // Create or update the Subdomains object
        if ($this->subdomains === null) {
            $this->subdomains = new Subdomains($parts);
        } else {
            $this->subdomains->fromString($parts);
        }

        // Create or update the Port object
        $implicitPort = ($this->isSecure())
            ? Port::getSecureHttpPort()
            : Port::getInsecureHttpPort();
        if ($this->port === null) {
            $this->port = new Port($this->urlParts['port'] ?? $implicitPort);
        } else {
            $this->port->fromInt($this->urlParts['port'] ?? $implicitPort);
        }

        // Create or update the Slugs object
        if ($this->slugs === null) {
            $this->slugs = new Slugs($this->urlParts['path'] ?? null);
        } else {
            $this->slugs->fromString($this->urlParts['path'] ?? null);
        }

        // Create or update the Parameters object
        if ($this->parameters === null) {
            $this->parameters = new Parameters($this->urlParts['query'] ?? null);
        } else {
            $this->parameters->fromString($this->urlParts['query'] ?? null);
        }

        // Create or update the Fragment object
        if ($this->fragment === null) {
            $this->fragment = new Fragment($this->urlParts['fragment'] ?? null);
        } else {
            $this->fragment->fromString($this->urlParts['fragment'] ?? null);
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

    /**
     * @return string
     */
    public function getDomainBase(): string
    {
        return $this->domainBase;
    }

    /**
     * @param string $domainBase
     * @return self
     */
    public function setDomainBase(string $domainBase): self
    {
        $this->domainBase = trim($domainBase);

        return $this;
    }

    /**
     * @return HttpAuth
     */
    public function getHttpAuth(): HttpAuth
    {
        return $this->httpAuth;
    }

    /**
     * @return Subdomains
     */
    public function getSubdomains(): Subdomains
    {
        return $this->subdomains;
    }

    /**
     * @return Tld
     */
    public function getTld(): Tld
    {
        return $this->tld;
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
}

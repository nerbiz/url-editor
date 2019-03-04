<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidHostException;

class Host implements Stringable
{
    /**
     * @var Subdomains
     */
    protected $subdomains;

    /**
     * @var Tld
     */
    protected $tld;

    /**
     * The full host name
     * @var string
     */
    protected $host;

    /**
     * The host without subdomains and TLD
     * @var string
     */
    protected $basename;

    /**
     * @param string $host
     */
    public function __construct(string $host)
    {
        $this->fromString($host);
    }

    /**
     * Get the originally injected host
     * @return string
     */
    public function getOriginal(): string
    {
        return $this->host;
    }

    /**
     * Get the host without subdomains and TLD
     * @return string
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * @param string $basename
     * @return self
     */
    public function setBasename(string $basename): self
    {
        if (strpos($basename, '.') !== false) {
            throw new InvalidHostException(sprintf(
                "%s(): basename cannot contain periods, use subdomains or TLD instead ('%s' given)",
                __METHOD__,
                $basename
            ));
        }

        $this->basename = trim($basename);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $host): self
    {
        $host = preg_replace('~^https?://~', '', trim($host, '/ '));
        $this->host = $host;

        // Create or update the TLD object
        if ($this->tld === null) {
            $this->tld = new Tld($this);
        } else {
            $this->tld->fromHost($this);
        }

        // Create or update the Subdomains object
        if ($this->subdomains === null) {
            $this->subdomains = new Subdomains($this);
        } else {
            $this->subdomains->fromHost($this);
        }

        // Set the basename of the host, by removing subdomains and TLD
        $subdomains = $this->getSubdomains()->toString();
        $tld = $this->getTld()->toString();
        $basename = trim(mb_substr($this->host, strlen($subdomains)), '.');
        $basename = trim(mb_substr($basename, 0, (0 - strlen($tld))), '.');
        $this->setBasename($basename);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        $subdomains = $this->getSubdomains()->toString();

        return sprintf(
            '%s%s.%s',
            ($subdomains !== '')
                ? $subdomains . '.'
                : '',
            $this->getBasename(),
            $this->getTld()->toString()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
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
}

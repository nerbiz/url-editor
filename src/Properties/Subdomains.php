<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;

class Subdomains implements Stringable, Arrayable, Jsonable
{
    /**
     * @var Host
     */
    protected $host;

    /**
     * @var string
     */
    protected $subdomains;

    /**
     * @param Host $host The host to derive the subdomains from
     */
    public function __construct(Host $host)
    {
        $this->host = $host;
        $this->fromHost($host);
    }

    /**
     * Derive the subdomains from a host
     * @param Host $host
     * @return self
     */
    public function fromHost(Host $host): self
    {
        $tld = $this->host->getTld()->toString();
        $hostWithoutTld = trim(substr($host->getOriginal(), 0, (0 - strlen($tld))), '.');
        $parts = explode('.', $hostWithoutTld);

        // There are no subdomains if there are less than 2 parts
        if (count($parts) < 2) {
            return $this->remove();
        }

        array_pop($parts);
        return $this->fromArray($parts);
    }

    /**
     * Remove the subdomains
     * @return self
     */
    public function remove()
    {
        return $this->fromString('');
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $subdomains): self
    {
        // Trim dots and spaces
        $this->subdomains = trim($subdomains, '. ');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->subdomains;
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
    public function fromArray(array $subdomains): self
    {
        if (count($subdomains) < 1) {
            return $this->remove();
        }

        $this->subdomains = implode('.', array_values($subdomains));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter(explode('.', $this->subdomains));
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

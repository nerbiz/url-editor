<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;
use Nerbiz\UrlEditor\Traits\HasArray;

class Subdomains implements Stringable, Arrayable, Jsonable
{
    use HasArray;

    /**
     * @param Host $host The host to derive the subdomains from
     */
    public function __construct(Host $host)
    {
        $this->fromHost($host);
    }

    /**
     * Derive the subdomains from a host
     * @param Host $host
     * @return self
     */
    public function fromHost(Host $host): self
    {
        $tld = $host->getTld()->toString();
        $hostWithoutTld = trim(substr($host->getOriginal(), 0, (0 - strlen($tld))), '.');
        $parts = explode('.', $hostWithoutTld);

        array_pop($parts);
        return $this->fromArray($parts);
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $subdomains): self
    {
        // Trim dots and spaces
        $subdomains = trim($subdomains, '. ');
        $this->items = array_values(explode('.', $subdomains));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return implode('.', $this->items);
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
        $this->items = array_values($subdomains);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->items;
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

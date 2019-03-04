<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;
use Nerbiz\UrlEditor\Exceptions\InvalidTldException;
use Nerbiz\UrlEditor\Traits\HasArray;

class Tld implements Stringable, Arrayable, Jsonable
{
    use HasArray;

    /**
     * The complete list of all TLDs
     * @var array
     */
    protected $validTldList;

    /**
     * @param Host $host The host to derive the TLD from
     */
    public function __construct(Host $host)
    {
        $this->setValidTldList();
        $this->fromHost($host);
    }

    /**
     * @param array|null $tldList Sets the default list if null
     * @return self
     * @see https://data.iana.org/TLD/tlds-alpha-by-domain.txt
     */
    public function setValidTldList(?array $tldList = null): self
    {
        // Get the TLD list from the file
        if ($tldList === null) {
            $listFile = rtrim(dirname(__FILE__, 3), '/') . '/resources/iana-tlds.txt';
            $listFileContents = file_get_contents($listFile);

            // Get all the lines that are not comments and convert them to lowercase
            preg_match_all('~^(?<tld>[^#].+)~m', $listFileContents, $matches);
            $this->validTldList = array_map('strtolower', $matches['tld']);
        } else {
            $this->validTldList = $tldList;
        }

        return $this;
    }

    /**
     * Derive the TLD from a host
     * @param Host $host
     * @return self
     */
    public function fromHost(Host $host): self
    {
        $tlds = [];
        // Reverse the parts, so the TLD(s) are the first items
        $hostParts = array_reverse(explode('.', $host->getOriginal()));

        foreach ($hostParts as $hostPart) {
            // Keep the TLD if it matches
            if (in_array(strtolower($hostPart), $this->validTldList, true)) {
                $tlds[] = $hostPart;
            } else {
                // Stop looping when no match was found
                break;
            }
        }

        // Reverse the array again, to get the original order
        $this->fromArray(array_reverse($tlds));

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidTldException
     */
    public function fromString(string $tld): self
    {
        // Trim dots and spaces
        $tld = trim($tld, '. ');
        $this->items = $this->fromArray(explode('.', $tld));

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
     * @throws InvalidTldException
     */
    public function fromArray(array $tld): self
    {
        $tld = array_values(array_filter($tld));

        // See if any invalid TLDs are given
        foreach ($tld as $tldPart) {
            if (! in_array(strtolower($tldPart), $this->validTldList, true)) {
                throw new InvalidTldException(sprintf(
                    "%s() expects a valid TLD, '%s' in '%s' is invalid",
                    __METHOD__,
                    $tldPart,
                    $tld
                ));
            }
        }

        $this->items = $tld;

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

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
     * @var array|null
     */
    protected static $validTldList = null;

    /**
     * @see https://data.iana.org/TLD/tlds-alpha-by-domain.txt
     */
    public function __construct()
    {
        // Set the valid TLD list if it's not set yet
        if (static::$validTldList === null) {
            $listFile = rtrim(dirname(__FILE__, 3), '/') . '/resources/iana-tlds.php';
            static::$validTldList = require $listFile;

            // Add the 'localhost' TLD
            static::$validTldList[] = 'localhost';
        }
    }

    /**
     * @param array $tldList Sets the default list
     * @return self
     */
    public function setValidTldList(array $tldList): self
    {
        static::$validTldList = $tldList;

        return $this;
    }

    /**
     *
     * @param string $hostName
     * @return self
     */
    public function fromHost(string $hostName): self
    {
        $tlds = [];
        // Reverse the parts, so the TLD(s) are the first items
        $parts = array_reverse(explode('.', $hostName));

        foreach ($parts as $key => $hostPart) {
            // Skip the last entry, because that is the domain name
            // 'localhost' -> no TLD
            // 'localhost.dev' -> .dev
            // 'test.localhost.dev' -> .localhost.dev
            if ($key === (count($parts) - 1)) {
                break;
            }

            // Keep the TLD if it matches
            if (in_array(strtolower($hostPart), static::$validTldList, true)) {
                $tlds[] = $hostPart;
            } else {
                // Stop looping when no match was found
                break;
            }
        }

        // Reverse the array again, to get the original order
        return $this->fromArray(array_reverse($tlds));
    }

    /**
     * {@inheritdoc}
     * @throws InvalidTldException
     */
    public function fromString(string $tld): self
    {
        return $this->fromArray(explode('.', $tld));
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
        $tld = array_values(array_filter(array_map(function ($item) {
            // Trim dots and spaces
            return trim($item, '. ');
        }, $tld)));

        // See if any invalid TLDs are given
        foreach ($tld as $tldPart) {
            if (! in_array(strtolower($tldPart), static::$validTldList, true)) {
                throw new InvalidTldException(sprintf(
                    "%s() expects a valid TLD, '%s' is invalid",
                    __METHOD__,
                    $tldPart
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

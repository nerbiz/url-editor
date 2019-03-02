<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;
use Nerbiz\UrlEditor\Exceptions\InvalidTldException;

class Tld implements Stringable, Arrayable, Jsonable
{
    /**
     * @var Host
     */
    protected $host;

    /**
     * The complete list of all TLDs
     * @var array
     */
    protected $validTldList;

    /**
     * The TLD of the host
     * @var string
     */
    protected $tld;

    /**
     * @param Host $host The host to derive the TLD from
     */
    public function __construct(Host $host)
    {
        $this->setValidTldList();
        $this->host = $host;
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

        // Set the TLD by imploding the TLDs
        $tlds = array_reverse($tlds);
        $this->tld = implode('.', $tlds);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidTldException
     */
    public function fromString(string $tld): self
    {
        $tld = trim($tld, '.');

        // See if any invalid TLDs are given
        $tldParts = explode('.', $tld);
        foreach ($tldParts as $tldPart) {
            if (! in_array(strtolower($tldPart), $this->validTldList, true)) {
                throw new InvalidTldException(sprintf(
                    "%s() expects a valid TLD, '%s' in '%s' is invalid",
                    __METHOD__,
                    $tldPart,
                    $tld
                ));
            }
        }

        $this->tld = $tld;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->tld;
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
        return $this->fromString(implode('.', array_values($tld)));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return explode('.', trim($this->tld, '.'));
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
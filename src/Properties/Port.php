<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Intable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidPortException;

class Port implements Intable, Stringable
{
    /**
     * The port of a URL
     * @var int
     */
    protected $port;

    /**
     * Ports to ignore when outputting as string
     * For instance: http://example.com is implicitly http://example.com:80
     * @var int[]
     */
    protected $ignoredPorts = [80];

    /**
     * @param int $port The host to derive the port number from
     */
    public function __construct(int $port)
    {
        $this->fromInt($port);
    }

    /**
     * {@inheritdoc}
     */
    public function fromInt(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toInt(): int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $port): self
    {
        // Trim colons and spaces
        $trimmedPort = trim($port, ': ');

        if (!is_numeric($trimmedPort)) {
            throw new InvalidPortException(sprintf(
                "%s() expects a numeric string (optionally with a colon), '%s' given",
                __METHOD__,
                is_object($port) ? get_class($port) : $port
            ));
        }

        $this->port = intval($trimmedPort, 10);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @param bool $force Force output, even if the port number is ignored
     */
    public function toString(bool $force = false): string
    {
        // Don't return an ignored port number
        if (! $force && in_array($this->port, $this->ignoredPorts, true)) {
            return '';
        }

        return strval($this->toInt());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

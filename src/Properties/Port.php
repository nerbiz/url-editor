<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Intable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidPortException;

class Port implements Intable, Stringable
{
    /**
     * The default port number for insecure HTTP connections
     * @var int
     */
    protected static $insecureHttpPort = 80;

    /**
     * The default port number for secure HTTP connections
     * @var int
     */
    protected static $secureHttpPort = 443;

    /**
     * The port of a URL
     * @var int
     */
    protected $port;

    /**
     * Ports to ignore when outputting as string
     * For instance:
     * http://example.com is implicitly http://example.com:80 by default
     * https://example.com is implicitly http://example.com:443 by default
     * @var int[]
     */
    protected $implicitPorts = [];

    /**
     * @param int $port The host to derive the port number from
     */
    public function __construct(int $port)
    {
        // Set the implicit ports
        $this->implicitPorts = [
            static::$insecureHttpPort,
            static::$secureHttpPort,
        ];

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
        if (! $force && in_array($this->port, $this->implicitPorts, true)) {
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

    /**
     * @return int
     */
    public static function getInsecureHttpPort(): int
    {
        return self::$insecureHttpPort;
    }

    /**
     * @param int $insecureHttpPort
     * @return void
     */
    public static function setInsecureHttpPort(int $insecureHttpPort): void
    {
        self::$insecureHttpPort = $insecureHttpPort;
    }

    /**
     * @return int
     */
    public static function getSecureHttpPort(): int
    {
        return self::$secureHttpPort;
    }

    /**
     * @param int $secureHttpPort
     * @return void
     */
    public static function setSecureHttpPort(int $secureHttpPort): void
    {
        self::$secureHttpPort = $secureHttpPort;
    }
}

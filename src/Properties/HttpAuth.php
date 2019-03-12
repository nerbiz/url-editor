<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Arrayable;
use Nerbiz\UrlEditor\Contracts\Jsonable;
use Nerbiz\UrlEditor\Contracts\Stringable;
use Nerbiz\UrlEditor\Exceptions\InvalidHttpAuthException;
use Nerbiz\UrlEditor\Exceptions\InvalidJsonException;

class HttpAuth implements Stringable, Arrayable, Jsonable
{
    /**
     * The username for the HTTP Authentication
     * @var string
     */
    protected $username;

    /**
     * The password for the HTTP Authentication
     * @var string
     */
    protected $password;

    /**
     * @param string|null $username
     * @param string|null $password
     */
    public function __construct(?string $username = null, ?string $password = null)
    {
        if (isset($username, $password)) {
            $this->fromArray(compact('username', 'password'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(string $credentials): self
    {
        // Trim at-symbols, colons and spaces
        $credentials = trim($credentials, '@: ');

        return $this->fromArray(explode(':', $credentials));
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        if (isset($this->username, $this->password)) {
            return sprintf('%s:%s', $this->username, $this->password);
        }

        return '';
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
    public function fromArray(array $credentials): self
    {
        if (count($credentials) < 2) {
            throw new InvalidHttpAuthException(sprintf(
                "%s() expects an array with (at least) 2 entries, '%s' given",
                __METHOD__,
                is_object($credentials) ? get_class($credentials) : json_encode($credentials)
            ));
        }

        // Get the username and password from explicit array keys if they exist
        if (isset($credentials['username'], $credentials['password'])) {
            $this->username = $credentials['username'];
            $this->password = $credentials['password'];
        } else {
            // Otherwise use the first 2 array values
            $credentials = array_values($credentials);
            $this->username = $credentials[0];
            $this->password = $credentials[1];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (isset($this->username, $this->password)) {
            return [
                'username' => $this->username,
                'password' => $this->password,
            ];
        }

        return [];
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

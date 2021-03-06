<?php

namespace Nerbiz\UrlEditor\Properties;

use Nerbiz\UrlEditor\Contracts\Stringable;

class Fragment implements Stringable
{
    /**
     * The fragment (hash) of a URL
     * @var string
     */
    protected $fragment = '';

    public function __construct(?string $fragment = null)
    {
        if ($fragment !== null) {
            $this->fromString($fragment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromString(?string $fragment = null): self
    {
        // Trim hashes and spaces
        $this->fragment = trim($fragment, '# ');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

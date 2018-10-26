<?php

namespace Nerbiz\UrlEditor\Properties;

class Fragment
{
    /**
     * The fragment (hash) of a URL
     * @var string
     */
    protected $fragment = '';

    public function __construct(string $fragment = null)
    {
        if ($fragment !== null) {
            $this->set($fragment);
        }
    }

    /**
     * @param  string $fragment
     * @return self
     */
    public function set(string $fragment) : self
    {
        $this->fragment = trim(str_replace('#', '', $fragment));

        return $this;
    }

    /**
     * @return string
     */
    public function get() : string
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
}

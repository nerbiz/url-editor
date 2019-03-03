<?php

namespace Nerbiz\UrlEditor\Contracts;

interface Stringable
{
    /**
     * Create object values from a string
     * @param string $string
     * @return self
     */
    public function fromString(string $string);

    /**
     * Convert the object to a string
     * @return string
     */
    public function toString(): string;

    /**
     * Convert the object to a string
     * @return string
     */
    public function __toString(): string;
}

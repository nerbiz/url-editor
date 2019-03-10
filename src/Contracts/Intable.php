<?php

namespace Nerbiz\UrlEditor\Contracts;

interface Intable
{
    /**
     * Create object values from an integer
     * @param int $int
     * @return self
     */
    public function fromInt(int $int);

    /**
     * Convert the object to a string
     * @return int
     */
    public function toInt(): int;
}

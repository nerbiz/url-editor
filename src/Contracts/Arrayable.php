<?php

namespace Nerbiz\UrlEditor\Contracts;

interface Arrayable
{
    /**
     * Create object values from an array
     * @param array $array
     * @return self
     */
    public function fromArray(array $array);

    /**
     * Convert the object to an array
     * @return array
     */
    public function toArray(): array;
}

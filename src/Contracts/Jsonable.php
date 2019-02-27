<?php

namespace Nerbiz\UrlEditor\Contracts;

interface Jsonable
{
    /**
     * Convert the object to a JSON string
     * @return string
     */
    public function toJson(): string;
}

<?php

namespace Nerbiz\UrlEditor\Contracts;

interface Jsonable
{
    /**
     * Create object values from JSON
     * @param string $json
     * @return self
     */
    public function fromJson(string $json);

    /**
     * Convert the object to a JSON string
     * @return string
     */
    public function toJson(): string;
}

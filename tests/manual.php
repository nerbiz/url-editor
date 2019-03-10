<?php

$classesDir = dirname(dirname(__FILE__)) . '/src/';
foreach (array_unique(array_merge(
    // Contracts and traits first
    glob($classesDir . 'Contracts/*.php'),
    glob($classesDir . 'Traits/*.php'),
    glob($classesDir . '*.php'),
    glob($classesDir . '**/*.php')
)) as $filepath) {
    require $filepath;
}

$url = 'http://www.example.co.uk:8080/slug-1/slug-2?param-1=value-1&empty=&another-empty&param-2=value-2#element-id';
$urlEditor = new Nerbiz\UrlEditor\UrlEditor($url);

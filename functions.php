<?php

function filter(array $items, callable $callback): array
{
    return array_filter($items, $callback);
}

function dd($value)
{
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die();
}

function getURI(): string
{
    return parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
}

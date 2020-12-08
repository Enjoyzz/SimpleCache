<?php

include __DIR__ . '/../vendor/autoload.php';

$cache_psr16 = new Enjoys\SimpleCache\Cache(
    new Enjoys\SimpleCache\Drivers\FileCache([])
);

//$cache_psr16->set('my_key', 5);
var_dump($cache_psr16->get('my_key'));
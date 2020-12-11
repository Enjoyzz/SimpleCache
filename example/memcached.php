<?php

include __DIR__ . '/../vendor/autoload.php';

$cacher = new \Enjoys\SimpleCache\Cacher\Memcached(
    [
        'host' => 'memcached'
    ]
);

$set = $cacher->set('d1', 2);
var_dump($set, $cacher->get('d1'));
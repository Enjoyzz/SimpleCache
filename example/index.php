<?php

include __DIR__ . '/../vendor/autoload.php';

use Enjoys\SimpleCache\Cache;

$cache = Cache::store(Cache::FILECACHE, []);

$cache->set('my_key', 5);
var_dump($cache->get('my_key'));

//var_dump();
var_dump( (new DateTime('@0'))->add(new DateInterval('P10D')));
<?php

include __DIR__ . '/../vendor/autoload.php';

use Enjoys\SimpleCache\Cache;

$cache = Cache::store(\Enjoys\SimpleCache\Cacher\NullCacher::class);

$cache_id = 'http';
$cache->set($cache_id, 'test');
//var_dump($cache->get('my_keydd', fn()=>$cache->set('my_keyd', 555)));
var_dump($cache->get($cache_id, '_______yyyy'));


//var_dump( (new DateTime('@0'))->add(new DateInterval('P10D')));
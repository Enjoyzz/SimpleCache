<?php

include __DIR__ . '/../vendor/autoload.php';

use Enjoys\SimpleCache\Cache;

$cache = Cache::store(Cache::FILECACHE, []);


//var_dump($cache->get('my_keydd', fn()=>$cache->set('my_keyd', 555)));
var_dump($cache->get('my_dkseyd', new class($cache) {
    public $cache;
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    public function return(){
        $this->cache->set('my_dkseyd', 4);
        return 4;
    }
}));

var_dump($cache->get('my_keydd'));
//var_dump( (new DateTime('@0'))->add(new DateInterval('P10D')));
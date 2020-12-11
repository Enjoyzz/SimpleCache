<?php


namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\Cacher\Memcached;

class MemcachedTest extends TypicalTestKit
{
    private $host = 'memcached';
    private $port = 11211;

    protected function getInstance($options = [])
    {
       //
        if (empty($options)) {
            $options = [
                'host' => $this->host,
                'port' => $this->port,
            ];
        }
        $cacher = new Memcached($options);
        return $cacher;
    }




}
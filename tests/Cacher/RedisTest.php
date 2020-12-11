<?php


namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\Cacher\Redis;

class RedisTest extends TypicalTestKit
{

    protected function getInstance($options = ['host' => 'redis'])
    {
        return new Redis($options);
    }
}
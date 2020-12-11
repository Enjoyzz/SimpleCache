<?php


namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\Cacher\Memcached;
use PHPUnit\Framework\TestCase;

class MemcachedTest extends TestCase
{
    private $host = 'memcached';
    private $port = 11211;

    private function getInstance($options = [])
    {
       //
        if (empty($options)) {
            $options = [
                'host' => $this->host,
                'port' => $this->port,
            ];
        }
        $cacher = new Memcached($options);

        //$this->tearDown();

        return $cacher;
    }

    protected function tearDown(): void
    {

    }

    public function data(): array
    {
        $obj = new \stdClass();
        $obj->field = 'test';

        return [
            [1, 1],
            [0.1, 0.1],
            ['string', 'string'],
            ['object', $obj],
            ['boolean', true],
            ['null', null],
            ['array', [1, 2, 3]],
        ];
    }

    /**
     * @dataProvider data
     */
    public function testGet($key, $value)
    {
        $cacher = $this->getInstance();
        $cacher->set($key, $value);
        //var_dump($key, $value, $cacher->get($key));
        $this->assertEquals($value, $cacher->get($key));
    }

}
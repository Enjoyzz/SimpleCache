<?php


namespace Tests\Enjoys\SimpleCache\Cacher;


use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

abstract class TypicalTestKit extends TestCase
{
    /**
     * @param array $options
     * @return CacheInterface;
     */
    abstract protected function getInstance($options = []);

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
    public function test_simplecache($key, $value)
    {
        $cacher = $this->getInstance();


        $cacher->set($key, $value);

        //var_dump($key, $value, $cacher->get($key));
        $this->assertEquals($value, $cacher->get($key));
    }


    public function test_with_ttl()
    {
        $cache_id = 'id';
        $cache_value = 'val';
        $cacher = $this->getInstance();
        $cacher->set($cache_id, $cache_value, 3);
        $this->assertSame($cache_value, $cacher->get($cache_id));

        sleep(2);
        $this->assertSame($cache_value, $cacher->get($cache_id));

        sleep(3);
        $this->assertSame('clear', $cacher->get($cache_id, 'clear'));
    }

    public function test_delete()
    {
        $cacher = $this->getInstance();
        $cacher->set('cacheid', ['array']);
        $this->assertSame(['array'], $cacher->get('cacheid'));
        $cacher->delete('cacheid');
        $this->assertSame(null, $cacher->get('cacheid'));
    }

    public function test_multi()
    {
        $cacher = $this->getInstance();
        $cacher->setMultiple(
            [
                'cacheid1' => 'val1',
                'cacheid2' => ['val2'],
                'cacheid3' => 10,
            ]
        );
        $this->assertSame(
            [
                'cacheid1' => 'val1',
                'cacheid2' => ['val2'],
                'cacheid3' => 10,
                'cacheid4' => null,
            ],
            $cacher->getMultiple(
                [
                    'cacheid1',
                    'cacheid2',
                    'cacheid3',
                    'cacheid4',
                ]
            )
        );

        $this->assertSame(
            false,
            $cacher->deleteMultiple(
                [
                    'cacheid1',
                    'cacheid5'
                ]
            )
        );
        $this->assertSame(
            true,
            $cacher->deleteMultiple(
                [
                    'cacheid2',
                    'cacheid3',
                ]
            )
        );
    }

    public function testHas()
    {
        $cacher = $this->getInstance();
        $cacher->set(1, 1);
        $this->assertSame(true, $cacher->has(1));
        $this->assertSame(false, $cacher->has(2));
    }

    public function testTtlNegative()
    {
        $cacher = $this->getInstance();
        $cacher->set(1, 1);
        $this->assertSame(true, $cacher->has(1));
        $cacher->set(1, 1, -1);
        $this->assertSame(false, $cacher->has(1));
    }


    public function testSetMultiple()
    {
        $cacher = $this->getInstance();
        $cacher->setMultiple(
            [
                'cacheid1' => 'val1',
                'cacheid2' => ['val2'],
                'cacheid3' => 10,
            ],
            -1
        );
        $this->assertSame(false, $cacher->has('cacheid1'));
        $this->assertSame(false, $cacher->has('cacheid2'));
        $this->assertSame(false, $cacher->has('cacheid3'));
    }

    public function testClear()
    {
        $key = uniqid('key');
        $cacher = $this->getInstance();
        $cacher->setMultiple([1,2,3]);
        $this->assertSame(1, $cacher->get(0));
        $this->assertSame(2, $cacher->get(1));
        $cacher->clear();
        $this->assertSame(null, $cacher->get(0));
        $this->assertSame(null, $cacher->get(1));
        $this->assertSame(null, $cacher->get(2));
    }
}
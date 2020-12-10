<?php


namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\Cacher\NullCacher;
use PHPUnit\Framework\TestCase;

class NullCacherTest extends TestCase
{
    public function data(): array
    {
        $obj = new \stdClass();
        $obj->field = 'test';

        return [
            [1, 'int_key'],
            [2.1, 'float_key'],
            ['string', 'string_key'],
        ];
    }

    /**
     * @dataProvider data
     */
    public function testGet($key, $value)
    {
        $c = new NullCacher();
        $this->assertEquals($value, $c->get($key, $value));
        $this->assertEquals(null, $c->get($key));
    }

    /**
     * @dataProvider data
     */

    public function testSet($key, $value)
    {
        $c = new NullCacher();
        $c->set($key, $value);
        $this->assertEquals(null, $c->get($key));
    }

    /**
     * @dataProvider data
     */
    public function testDelete($key)
    {
        $c = new NullCacher();
        $this->assertSame(true, $c->delete($key));
    }

    public function testClear()
    {
        $c = new NullCacher();
        $this->assertSame(true, $c->clear());
    }

    public function testGetMultiple()
    {
        $keys = array_column($this->data(), '0');
        $c = new NullCacher();

        $this->assertSame(
            array_combine(
                $keys,
                array_fill(
                    0,
                    count($keys),
                    'default_value'
                )
            ),
            $c->getMultiple($keys, 'default_value')
        );
    }

    public function testSetMultiple()
    {
        $keys = array_column($this->data(), '0');
        $c = new NullCacher();
        $this->assertSame(true, $c->setMultiple($keys));
    }

    public function testDeleteMultiple()
    {
        $keys = array_column($this->data(), '0');
        $c = new NullCacher();
        $this->assertSame(true, $c->deleteMultiple($keys));
    }

    /**
     * @dataProvider data
     */
    public function testHas($key)
    {
        $c = new NullCacher();
        $this->assertSame(false, $c->has($key));

    }

}
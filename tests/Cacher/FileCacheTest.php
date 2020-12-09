<?php
namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher\FileCache;
use Enjoys\SimpleCache\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{

    public function data(): array
    {
        $obj = new \stdClass();
        $obj->field = 'test';

        return [
            [1, 'int_key'],
            [2.1, 'float_key'],
            ['string', 'string_key'],
            ['object', $obj],
            ['boolean', true],
            ['null', null],
            ['AZaz_.09', 'legalkey_chars'],
            ['.0123456789qwertyuiopasdfghjklzxcvbnm.0123456789qwertyuiopasdfg', 'legalkey_chars_count64'],
            ['array', [0 => 'mixed', 'array' => $obj, 'settting' => 2.1, 'bool' => false]],
        ];
    }

    /**
     * @dataProvider data
     * @param $key
     * @param $value
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function test_simplecache($key, $value)
    {
        $cacher = new FileCache();

        $cacher->set($key, $value);

        //var_dump($key, $value, $cacher->get($key));
        $this->assertEquals($value, $cacher->get($key));
    }


    public function test_with_ttl()
    {
        $cacher = new FileCache();
        $cacher->set('testkey', 'testvalue', 5);
        $this->assertSame('testvalue', $cacher->get('testkey'));

        sleep(4);
        $this->assertSame('testvalue', $cacher->get('testkey'));

        sleep(2);
        $this->assertSame('clear', $cacher->get('testkey', 'clear'));
    }

    public function test_delete()
    {
        $cacher = new FileCache();
        $cacher->set('cacheid', ['array']);
        $this->assertSame(['array'], $cacher->get('cacheid'));
        $cacher->delete('cacheid');
        $this->assertSame(null, $cacher->get('cacheid'));
    }

    public function test_multi(): FileCache
    {
        $cacher = new FileCache();
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

        return $cacher;
    }

    /**
     * @depends test_multi
     * @param FileCache $cacher
     */
    public function test_multi_delete(FileCache $cacher)
    {
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
}
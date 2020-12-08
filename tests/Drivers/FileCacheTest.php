<?php


namespace Tests\Enjoys\SimpleCache;


use Enjoys\SimpleCache\Drivers\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    public function test_simplecache()
    {
        $cacher = new FileCache();
        $cacher->save('1', 2);
        $this->assertSame(2, $cacher->get(1));
    }

    public function test_with_ttl()
    {
        $cacher = new FileCache();
        $cacher->save(2, 3, 5);
        $this->assertSame(3, $cacher->get(2));
        sleep(4);
        $this->assertSame(3, $cacher->get(2));
        sleep(2);
        $this->assertSame('clear', $cacher->get(2, 'clear'));
    }

    public function test_delete()
    {
        $cacher = new FileCache();
        $cacher->save('cacheid', ['array']);
        $this->assertSame(['array'], $cacher->get('cacheid'));
        $cacher->delete('cacheid');
        $this->assertSame(null, $cacher->get('cacheid'));
    }

    public function test_multi()
    {
        $cacher = new FileCache();
        $cacher->saveMulti(
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
            $cacher->getMulti(
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
     */
    public function test_multi_delete($cacher)
    {
        $this->assertSame(
            false,
            $cacher->deleteMulti(
                [
                    'cacheid1',
                    'cacheid5'
                ]
            )
        );
        $this->assertSame(
            true,
            $cacher->deleteMulti(
                [
                    'cacheid2',
                    'cacheid3',
                ]
            )
        );
    }
}
<?php

namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher\FileCache;
use Enjoys\SimpleCache\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{

    private string $cache_path = __DIR__.'/../.cache';
    private array $undeletedFiles = [
        '.gitkeep',
    ];

    protected function tearDown(): void
    {
        $di = new \RecursiveDirectoryIterator($this->cache_path, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileInfo $file */
        foreach ($ri as $file ) {
            if(in_array($file->getFilename(), $this->undeletedFiles)) {
                continue;
            }
            $file->isDir() ?  rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }
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
     * @param $key
     * @param $value
     * @throws CacheException
     * @throws InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function test_simplecache($key, $value)
    {
        $cacher = new FileCache(['path' => $this->cache_path]);


        $cacher->set($key, $value);

        //var_dump($key, $value, $cacher->get($key));
        $this->assertEquals($value, $cacher->get($key));
    }


    public function test_with_ttl()
    {
        $cache_id = 'id';
        $cache_value = 'val';
        $cacher = new FileCache(['path' => $this->cache_path]);
        $cacher->set($cache_id, $cache_value, 3);
        $this->assertSame($cache_value, $cacher->get($cache_id));

        sleep(2);
        $this->assertSame($cache_value, $cacher->get($cache_id));

        sleep(3);
        $this->assertSame('clear', $cacher->get($cache_id, 'clear'));
    }

    public function test_delete()
    {
        $cacher = new FileCache(['path' => $this->cache_path]);
        $cacher->set('cacheid', ['array']);
        $this->assertSame(['array'], $cacher->get('cacheid'));
        $cacher->delete('cacheid');
        $this->assertSame(null, $cacher->get('cacheid'));
    }

    public function test_multi()
    {
        $cacher = new FileCache(['path' => $this->cache_path]);
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


}
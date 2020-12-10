<?php
/** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection PhpDocSignatureInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher\FileCache;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\SimpleCache\Reflection;

class FileCacheTest extends TestCase
{

    use Reflection;

    private string $cache_path = __DIR__ . '/../.cache';
    private array $undeletedFiles = [
        '.gitkeep',
    ];

    /**
     * @param array $options
     * @return FileCache
     * @throws CacheException
     */
    private function getInstance($options = [])
    {
        $this->tearDown();
        if (empty($options)) {
            $options = ['path' => $this->cache_path];
        }
        return new FileCache($options);
    }

    protected function tearDown(): void
    {
        $di = new \RecursiveDirectoryIterator($this->cache_path, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileInfo $file */
        foreach ($ri as $file) {
            if (in_array($file->getFilename(), $this->undeletedFiles)) {
                continue;
            }
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
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


    public function testGC()
    {
        $key = 1;
        $cacher = $this->getInstance(['path' => $this->cache_path, 'gcProbability' => 1000000]);
        $cacher->set($key, 1, 1);
        $filename = $this->getPrivateMethod(FileCache::class, 'getFilePath')->invokeArgs($cacher, [$key, false]);
        $this->assertFileExists($filename);
        sleep(1);
        $cacher->set(2, 1);
        $this->assertFileDoesNotExist($filename);
    }

    public function testClear()
    {
        $key = uniqid('key');
        $cacher = $this->getInstance();
        $cacher->set($key, 1, 1);
        $filename = $this->getPrivateMethod(FileCache::class, 'getFilePath')->invokeArgs($cacher, [$key, false]);
        $this->assertFileExists($filename);
        $cacher->clear();
        $this->assertFileDoesNotExist($filename);
    }

    public function ttl()
    {
        return [
            [null, (new \ReflectionClass(FileCache::class))->getConstant('DEFAULT_TTL')],
            [-1, -1],
            [1, 1],
            [(new \DateInterval('P2Y4DT6H8M')), 63439680],
        ];
    }

    /**
     * @dataProvider ttl
     */
    public function testGetTTL($ttl, $expect)
    {
        $cacher = $this->getInstance();
        $getTTL = $this->getPrivateMethod(FileCache::class, 'getTTL');
        $result = $getTTL->invokeArgs($cacher, [$ttl]);
        $this->assertSame($expect, $result);
    }

    public function testInvalidMakeDir()
    {
        $this->expectException(CacheException::class);
        $this->getInstance(['path' => '/mycache']);
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


}
<?php
/** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection PhpDocSignatureInspection */

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher\FileCache;
use PHPUnit\Framework\TestCase;
use Tests\Enjoys\SimpleCache\Reflection;

class FileCacheTest extends TypicalTestKit
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
    protected function getInstance($options = [])
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


    public function testInvalidMakeDir()
    {
        $this->expectException(CacheException::class);
        $this->getInstance(['path' => '/mycache']);
    }



}
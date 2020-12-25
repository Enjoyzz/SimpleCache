<?php

declare(strict_types=1);

namespace Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher;
use Enjoys\SimpleCache\InvalidArgumentException;

class FileCache extends Cacher
{



    private const UNDELETED_FILES = [
        '.gitkeep',
        '.gitignore',
    ];

    /**
     * @var string
     */
    private string $path;
    private int $gcProbability;


    /**
     * FileCache constructor.
     * @param array $options
     *  path string /tmp/cache directory for the save cache files
     *  gcProbability int 10 the probability (parts per million) that garbage collection (GC) should be performed
     *      when storing a piece of data in the cache. Defaults to 10, meaning 0.001% chance.
     *      This number should be between 0 and 1000000. A value 0 means no GC will be performed at all.
     * @throws CacheException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->path = (string)$this->getOption('path', '/tmp/cache');
        $this->gcProbability = (int)$this->getOption('gcProbability', 10);
        $this->makeDir($this->path);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function get($key, $default = null)
    {
        $key = $this->checkValidKey($key);
        $filename = $this->getFilePath($key, false);
        if (!file_exists($filename)) {
            return $this->handlingDefaultValue($default);
        }

        $filetime = @filemtime($filename);

        if ($filetime === false) {
            $this->delete($key);
            return $this->handlingDefaultValue($default);
        }

        if ($this->checkTtl($filetime) === false) {
            $this->delete($key);
            return $this->handlingDefaultValue($default);
        }
        return unserialize(file_get_contents($filename));
    }


    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->gc();

        $key = $this->checkValidKey($key);

        $ttl = $this->normalizeTtl($ttl);

        // var_dump( $ttl);
        if ($ttl < time()) {
            $this->delete($key);
            return false;
        }

        $filename = $this->getFilePath($key);

        //var_dump($filename, $ttl);

        $f = fopen($filename, 'w+');
        if (flock($f, LOCK_EX)) {
            fwrite($f, serialize($value));
            flock($f, LOCK_UN);
        }
        fclose($f);

        //установка метки времени для сброса кэша
        touch($filename, $ttl);

        return true;
    }


    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function delete($key): bool
    {
        $key = $this->checkValidKey($key);
        $filename = $this->getFilePath($key, false);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool
    {
        $this->removeCacheFiles();
        return true;
    }


    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getMultiple($keys, $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $result = true;
        $good_keys = [];
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $result = false;
                break;
            }
            $good_keys[] = $key;
        }

        if ($result === false) {
            $this->deleteMultiple($good_keys);
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function has($key): bool
    {
        $key = $this->checkValidKey($key);
        $filename = $this->getFilePath($key, false);
        return file_exists($filename);
    }

    /**
     * @param string $path
     * @param int $permissions
     * @return void
     * @throws CacheException
     */
    private function makeDir(string $path, int $permissions = 0777): void
    {
        if (!is_dir($path)) {
            if (@mkdir($path, $permissions, true) === false) {
                $error = error_get_last();
                throw new CacheException(sprintf("Не удалось создать директорию: %s! Причина: %s", $path, $error['message']));
            }
        }
    }

    /**
     * @param string $key
     * @param bool $mkdir
     * @return string
     * @throws CacheException
     */
    private function getFilePath(string $key, $mkdir = true): string
    {
        $filename = $this->getFileName($key);
        $path = $this->path . DIRECTORY_SEPARATOR;

        foreach (array($filename[0], $filename[1], $filename[2], $filename[3]) as $dir) {
            $path .= $dir . DIRECTORY_SEPARATOR;
        }

        // var_dump($path);
        if ($mkdir === true) {
            $this->makeDir($path);
        }

        return $path . $filename;
    }


    /**
     * @param int $ttl
     * @return bool
     */
    private function checkTtl(int $ttl): bool
    {
        if ($ttl >= time()) {
            return true;
        }
        return false;
    }

    private function getFileName(string $key): string
    {
        return md5($key);
    }


    /**
     * @param bool $gc Garbage Collector
     */
    private function removeCacheFiles(bool $gc = false): void
    {
        $di = new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileInfo $file */
        foreach ($ri as $file) {
            if (in_array($file->getFilename(), self::UNDELETED_FILES)) {
                continue;
            }
            if ($gc) {
                if ($file->isDir()) {
                    continue;
                }

                $file->getMTime() > time() ?: unlink($file->getRealPath());
            } else {
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
        }
    }


    /**
     * @throws \Exception
     */
    private function gc()
    {
        if (\random_int(0, 1000000) < $this->gcProbability) {
            $this->removeCacheFiles(true);
        }
    }

}
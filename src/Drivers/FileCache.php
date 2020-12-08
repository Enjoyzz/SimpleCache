<?php

declare(strict_types=1);

namespace Enjoys\SimpleCache\Drivers;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Driver;

class FileCache extends Driver
{


    /**
     * @var mixed
     */
    private string $path;

    /**
     * @throws CacheException
     */
    protected function init(): void
    {
        $this->path = $this->getOption('path', '/tmp/cache');
        $this->makeDir($this->path);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     * @throws CacheException
     */
    public function get(string $key, $default = null)
    {
//        var_dump($key);
        $filename = $this->getFilePath($key, false);
        if (!file_exists($filename)) {
            return $default;
        }


        if ($this->checkTtl(filemtime($filename)) === false) {
            unlink($filename);
            return $default;
        }
        return unserialize(file_get_contents($filename));
    }


    /**
     * @param string $key
     * @param $value
     * @param null $ttl
     * @return bool
     * @throws CacheException
     */
    public function save(string $key, $value, $ttl = null): bool
    {
        $ttl ??= 31536000;
        $filename = $this->getFilePath($key);

        //var_dump($filename, $ttl);

        $f = fopen($filename, 'w+');
        if (flock($f, LOCK_EX)) {
            fwrite($f, serialize($value));
            flock($f, LOCK_UN);
        }
        fclose($f);

        //установка метки времени для сброса кэша
        touch($filename, time() + $ttl);

        return true;
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

    /**
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function delete(string $key): bool
    {
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
    public function clearCache(): bool
    {
        return true;
    }


    /**
     * @param array $keys
     * @param null $default
     * @return array
     * @throws CacheException
     */
    public function getMulti(array $keys, $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @param array $values
     * @param null $ttl
     * @return bool
     * @throws CacheException
     */
    public function saveMulti(array $values, $ttl = null): bool
    {
        $result = true;
        $good_keys = [];
        foreach ($values as $key => $value) {
            if (!$this->save($key, $value, $ttl)) {
                $result = false;
                break;
            }
            $good_keys[] = $key;
        }

        if ($result === false) {
            $this->deleteMulti($good_keys);
            return false;
        }

        return true;
    }


    /**
     * @param array $keys
     * @return bool
     * @throws CacheException
     */
    public function deleteMulti(array $keys): bool
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->delete($key);
        }
        return !in_array(false, $result);
    }

    /**
     * @param $key
     * @return bool
     * @throws CacheException
     */
    public function has($key): bool
    {
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
            if (mkdir($path, $permissions, true) === false) {
                throw new CacheException(sprintf("Не удалось создать директорию: %s", $path));
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

    private function getFileName(string $key): string
    {
        return md5($key);
    }
}
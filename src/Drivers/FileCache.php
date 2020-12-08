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

    protected function init(): void
    {
        $this->path = $this->getOption('path', '/tmp/cache');
        $this->makeDir($this->path);
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
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


    public function save(string $key, $value, $ttl = null)
    {
        $ttl ??= 31536000;
        $filename = $this->getFilePath($key, true);

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
     */
    public function delete(string $key)
    {
        $filename = $this->getFilePath($key, false);
        if(file_exists($filename)){
            return unlink($filename);
        }
        return false;

    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clearCache()
    {
        // TODO: Implement clear() method.
    }


    public function getMulti(array $keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function saveMulti(array $values, $ttl = null)
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


    public function deleteMulti(array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->delete($key);
        }
        return !in_array(false, $result);
    }

    public function has($key)
    {
        $filename = $this->getFilePath($key, false);
        return file_exists($filename);
    }

    /**
     * @param string $path
     * @param int $permissons
     * @param bool $recurcive
     * @return string
     * @throws \Exception
     */
    private function makeDir(string $path, int $permissons = 0777): void
    {
        if (!is_dir($path)) {
            if (mkdir($path, $permissons, true) === false) {
                throw new CacheException(sprintf("Не удалось создать директорию: %s", $path));
            }
        }
    }

    private function getFilePath(string $key, $makedir = true): string
    {
        $filename = $this->getFileName($key);
        $path = $this->path . DIRECTORY_SEPARATOR;

        foreach (array($filename[0], $filename[1], $filename[2], $filename[3]) as $dir) {
            $path .= $dir . DIRECTORY_SEPARATOR;
        }

        // var_dump($path);
        if ($makedir === true) {
            $this->makeDir($path);
        }

        return $path . $filename;
    }

    private function getFileName(string $key): string
    {
        return md5($key);
    }
}
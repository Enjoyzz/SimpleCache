<?php
declare(strict_types=1);

namespace Enjoys\SimpleCache\Drivers;


use Enjoys\SimpleCache\CacheException;
use Enjoys\Traits\Options;

class File implements DriverInterface
{

    use Options;
    /**
     * @var mixed
     */
    private string $path;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
        $this->path = $this->getOption('path', '/tmp/cache');
        $this->makeDir($this->path, 0777, true);
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


    public function set(string $key, $value, ?int $ttl = null)
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
        unlink($filename);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        // TODO: Implement getMultiple() method.
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        // TODO: Implement has() method.
    }

    /**
     * @param string $path
     * @param int $permissons
     * @param bool $recurcive
     * @return string
     * @throws \Exception
     */
    private function makeDir(string $path, int $permissons, bool $recurcive): void
    {
        if (!is_dir($path)) {
            if (mkdir($path, $permissons, $recurcive) === false) {
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
            $this->makeDir($path, 0777, true);
        }

        return $path . $filename;
    }

    private function getFileName(string $key): string
    {
        return md5($key);
    }
}
<?php

declare(strict_types=1);

namespace Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher;
use Enjoys\SimpleCache\InvalidArgumentException;

class FileCache extends Cacher
{

    private const DEFAULT_TTL = 31536000; // 1 year

    /**
     * @var mixed
     */
    private string $path;

    /**
     * FileCache constructor.
     * @param array $options
     * @throws CacheException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->path = $this->getOption('path', '/tmp/cache');
        $this->makeDir($this->path);
    }

    /**
     * @inheritDoc
     * @param scalar $key
     * @param mixed|null $default
     * @return mixed|null
     * @throws CacheException|InvalidArgumentException
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
     * @param scalar $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws CacheException|InvalidArgumentException
     */
    public function set($key, $value, $ttl = null): bool
    {
        $key = $this->checkValidKey($key);

        $ttl = $this->getTTL($ttl);

      // var_dump( $ttl);
//        if ($ttl < time()) {
//            $this->delete($key);
//            return false;
//        }

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
     * @param scalar $key
     * @return bool
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
        $this->removeCacheFiles($this->path, true);
        return true;
    }


    /**
     * @param iterable $keys
     * @param mixed|null $default
     * @return array
     * @throws CacheException
     * @throws InvalidArgumentException
     * @noinspection PhpMissingParamTypeInspection
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
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws CacheException
     * @throws InvalidArgumentException
     * @noinspection PhpMissingParamTypeInspection
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
     * @param iterable $keys
     * @return bool
     * @throws CacheException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function deleteMultiple($keys): bool
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->delete($key);
        }
        return !in_array(false, $result);
    }

    /**
     * @param scalar $key
     * @return bool
     * @throws CacheException
     * @throws InvalidArgumentException
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
     * @param null|int|\DateInterval $ttl
     * @return int
     */
    private function getTTL($ttl): int
    {
        if ($ttl instanceof \DateInterval) {
            return (new \DateTime('@0'))->add($ttl)->getTimestamp();
        }
        return $ttl ?? self::DEFAULT_TTL;
    }

    /**
     * @param null $path
     * @param bool $full
     */
    private function removeCacheFiles($path = null, bool $full = false): void
    {
//        $path ??= $this->path;
//
//        if (($handle = opendir($path)) !== false) {
//            while (false !== ($file = readdir($handle))) {
//                if (strncmp($file, '.', 1) === 0) {
//                    continue;
//                }
//                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
//                if (is_dir($fullPath)) {
//                    $this->removeCacheFiles($fullPath, $full);
//                    if (!$full && !@rmdir($fullPath)) {
//                        $error = error_get_last()['message'];
//                        throw new CacheException("Unable to remove directory '{$fullPath}': {$error['message']}");
//                    }
//                } elseif (!$full || ($full && @filemtime($fullPath) < time())) {
//                    if (!@unlink($fullPath)) {
//                        $error = error_get_last();
//                        throw new CacheException("Unable to remove file '{$fullPath}': {$error['message']}");
//                    }
//                }
//            }
//            closedir($handle);
//        }
    }

}
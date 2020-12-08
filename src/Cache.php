<?php
declare(strict_types=1);

namespace Enjoys\SimpleCache;


use Enjoys\SimpleCache\Drivers\DriverInterface;
use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    private DriverInterface $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param array $keys
     * @return void
     * @throws InvalidArgumentException
     */
    private function checkValidKey(array $keys): void
    {
        foreach ($keys as $key) {
            if (!\is_scalar($key) || strpbrk($key, '{}()/\@:')) {
                throw new InvalidArgumentException('key string is not a legal value.');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $this->checkValidKey([$key]);
        return $this->driver->get((string)$key, $default);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->checkValidKey([$key]);
        return $this->driver->save((string)$key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $this->checkValidKey([$key]);
        return $this->driver->delete((string)$key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->driver->clearCache();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $this->checkValidKey((array)$keys);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $this->checkValidKey(array_keys($values));
        return $this->driver->saveMulti((array)$values);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        $this->checkValidKey((array)$keys);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $this->checkValidKey([$key]);
        return $this->driver->has((string)$key);
    }
}
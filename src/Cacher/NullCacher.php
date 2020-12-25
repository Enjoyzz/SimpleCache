<?php


namespace Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher;
use Enjoys\SimpleCache\InvalidArgumentException;

class NullCacher extends Cacher
{

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function get($key, $default = null)
    {
        $this->checkValidKey($key);
        return $this->handlingDefaultValue($default);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function set($key, $value, $ttl = null)
    {
        $this->checkValidKey($key);
        return true;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function delete($key)
    {
        $this->checkValidKey($key);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[(string)$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function has($key)
    {
        $this->checkValidKey($key);
        return false;
    }
}
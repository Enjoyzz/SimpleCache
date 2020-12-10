<?php


namespace Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\Cacher;

class NullCacher extends Cacher
{

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $this->checkValidKey($key);
        return $this->handlingDefaultValue($default);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->checkValidKey($key);
        return true;
    }

    /**
     * @inheritDoc
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
     */
    public function has($key)
    {
        $this->checkValidKey($key);
        return false;
    }
}
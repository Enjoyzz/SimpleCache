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
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        // TODO: Implement set() method.
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        // TODO: Implement getMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        // TODO: Implement has() method.
    }
}
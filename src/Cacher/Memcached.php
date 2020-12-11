<?php
/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher;

class Memcached extends Cacher
{
    private \Memcached $memcached;

    private $memcachedFlags;

    /**
     * Memcached constructor.
     * @param array $options
     *  host string Point to the host where memcached is listening for connections
     *  port int Point to the port where memcached is listening for connections
     *  persistent bool Controls the use of a persistent connection. Default to <b>TRUE</b>
     * @throws CacheException
     */
    public function __construct(array $options = [])
    {
        //phpinfo();
        if (!class_exists('\Memcached')) {
            throw new CacheException('Memcached not installed');
        }

        parent::__construct($options);

        $this->memcached = new \Memcached();
        $this->memcached->addServer(
            $this->getOption('host', 'localhost'),
            $this->getOption('port', 11211)
        );
        //$this->getOption('persistent', true)

        $this->memcachedFlags = $this->getOption('flags', 0);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $key = $this->checkValidKey($key);

        $result = $this->memcached->get($key);

        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            $result = $this->handlingDefaultValue($default);
        }

        return $result;
    }


    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $key = $this->checkValidKey($key);

        return $this->memcached->set(
            $key,
            $value,
            $this->normalizeTtl($ttl)
        );
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->memcached->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->memcached->flush();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $ttl = $this->normalizeTtl($ttl);
        return $this->memcached->setMulti((array)$values, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->delete($key);
        }
        return !in_array(false, $result);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $key = $this->checkValidKey($key);
        $this->memcached->get($key);
        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return false;
        }
        return true;
    }
}
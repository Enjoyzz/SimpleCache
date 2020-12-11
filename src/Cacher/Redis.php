<?php


namespace Enjoys\SimpleCache\Cacher;


use Enjoys\SimpleCache\CacheException;
use Enjoys\SimpleCache\Cacher;

class Redis extends Cacher
{

    private $redis;

    /**
     * Redis constructor.
     * @throws CacheException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->redis = new \Redis();
        try {
            $this->redis->connect($this->getOption('host', '127.0.0.1'), $this->getOption('port', 6379));
        } catch (\RedisException $e) {
            throw new CacheException($e->getMessage());
        }

        $this->redis->setOption(\Redis::OPT_SERIALIZER, $this->getOption('serializer', \Redis::SERIALIZER_PHP));

        $this->options = $options;
    }


    public function get($key, $default = null)
    {
        $key = $this->checkValidKey($key);
        $result = $this->redis->get($key);
        if ($result === false) {
            $result = $this->handlingDefaultValue($default);
        }
        return $result;
    }

    public function set($key, $value, $ttl = null)
    {
        $ttl = $this->normalizeTtl($ttl, 0);
        if($ttl < 0){
            return $this->delete($key);
        }
        return $this->redis->set(
            $this->checkValidKey($key),
            $value,
            $ttl
        );
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $return =  $this->redis->del($key);
        if($return > 0) {
            return true;
        }

        return false;
    }

    public function clear()
    {
        return $this->redis->flushDB();
    }

    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[$key] = $this->set($key, $value, $ttl);
        }
        return $result;
    }


    public function has($key)
    {
        return (bool) $this->redis->exists($key);
    }
}
<?php
//declare(strict_types=1);
//
//namespace Enjoys\SimpleCache\Cacher;
//
//
//use Enjoys\SimpleCache\Cacher;
//use Enjoys\Traits\Options;
//
//class Memcached extends Cacher
//{
//    private \Memcache $memcache;
//
//    private $memcacheFlags;
//
//    public function __construct(array $options = [])
//    {
//        parent::__construct($options);
//        $this->memcache = new \Memcache();
//        $this->memcache->addServer(
//            $this->getOption('host', 'localhost'),
//            $this->getOption('port', '11211')
//        );
//
//        $this->memcacheFlags = $this->getOption('flags', null);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function get($key, $default = null)
//    {
//        $key = $this->checkValidKey($key);
//        $result =  $this->memcache->get($key, $this->memcacheFlags);
//        if($result === false) {
//            $result = $this->handlingDefaultValue($default);
//        }
//        return $result;
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function set($key, $value, $ttl = null)
//    {
//        $key = $this->checkValidKey($key);
//
//        if($this->has($key)){
//            return $this->memcache->replace($key, $value, $this->memcacheFlags, $ttl);
//        }
//
//        return $this->memcache->set($key, $value, $this->memcacheFlags, $ttl);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function delete($key)
//    {
//        return $this->memcache->delete($key);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function clear()
//    {
//        return $this->memcache->flush();
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function getMultiple($keys, $default = null)
//    {
//        $result = [];
//        foreach ($keys as $key) {
//            $result[$key] = $this->get($key, $default);
//        }
//
//        return $result;
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function setMultiple($values, $ttl = null)
//    {
//        $result = true;
//        $good_keys = [];
//        foreach ($values as $key => $value) {
//            if (!$this->set($key, $value, $ttl)) {
//                $result = false;
//                break;
//            }
//            $good_keys[] = $key;
//        }
//
//        if ($result === false) {
//            $this->deleteMultiple($good_keys);
//            return false;
//        }
//
//        return true;
//    }
//
//    /**
//     * Deletes multiple cache items in a single operation.
//     *
//     * @param iterable $keys A list of string-based keys to be deleted.
//     *
//     * @return bool True if the items were successfully removed. False if there was an error.
//     *
//     * @throws \Psr\SimpleCache\InvalidArgumentException
//     *   MUST be thrown if $keys is neither an array nor a Traversable,
//     *   or if any of the $keys are not a legal value.
//     */
//    public function deleteMultiple($keys)
//    {
//        $result = [];
//        foreach ($keys as $key) {
//            $result[] = $this->delete($key);
//        }
//        return !in_array(false, $result);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function has($key)
//    {
//        $key = $this->checkValidKey($key);
//        if(false === $this->memcache->get($key, $this->memcacheFlags)){
//            return false;
//        }
//        return true;
//    }
//}
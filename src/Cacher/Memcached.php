<?php
//declare(strict_types=1);
//
//namespace Enjoys\SimpleCache\Cacher;
//
//
//use Enjoys\Traits\Options;
//
//class Memcached implements DriverInterface
//{
//    use Options;
//
//    private \Memcache $memcache;
//
//    /**
//     * @var mixed
//     */
//    private $compress;
//
//    public function __construct(array $options = [])
//    {
//        $this->setOptions($options);
//
//        $this->memcache = new \Memcache();
//        $this->memcache->addServer(
//            $this->getOption('host', 'localhost'),
//            $this->getOption('port', '11211')
//        );
//        //compress = MEMCACHE_COMPRESSED
//        //$this->compress = $this->getOption('compress', 0);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function get($key, $default = null)
//    {
//        return $this->memcache->get($key);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function set($key, $value, $ttl = null)
//    {
//        $this->memcache->set($key, $value, $ttl);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function delete($key)
//    {
//        $this->memcache->delete($key);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function clear()
//    {
//        $this->memcache->flush();
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function getMultiple($keys, $default = null)
//    {
//        return $this->memcache->getMulti($keys);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function setMultiple($values, $ttl = null)
//    {
//        $this->memcache->setMulti($values, $ttl);
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
//        $this->memcache->deleteMulti($keys);
//    }
//
//    /**
//     * @inheritDoc
//     */
//    public function has($key)
//    {
//        // TODO: Implement has() method.
//    }
//}
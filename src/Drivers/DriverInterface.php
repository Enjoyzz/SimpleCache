<?php


namespace Enjoys\SimpleCache\Drivers;


use Psr\SimpleCache\CacheInterface;

interface DriverInterface
{
    public function __construct(array $options = []);
    public function clearCache();
    public function save(string $key, $value, $ttl = null);
    public function delete(string $key);
    public function getMulti(array $keys, $default = null);
    public function saveMulti(array $values, $ttl = null);
    public function deleteMulti(array $keys);
    public function has($key);
}
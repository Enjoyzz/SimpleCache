<?php
declare(strict_types=1);

namespace Enjoys\SimpleCache;


use Enjoys\SimpleCache\Cacher\FileCache;
use Psr\SimpleCache\CacheInterface;

class Cache
{
    public const FILECACHE = FileCache::class;
//    public const MEMCACHED = Memcached::class;

    /**
     * @param string $className
     * @param array $options
     * @return CacheInterface
     * @throws CacheException
     */
    public static function store(string $className, array $options = []): CacheInterface
    {
        if (class_exists($className)) {
            $cacher = new $className($options);

            if ($cacher instanceof CacheInterface) {
                return $cacher;
            }
        }


        throw new CacheException();
    }


}
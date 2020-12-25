<?php


namespace Enjoys\SimpleCache;


use Psr\SimpleCache\CacheInterface;

trait CacherTrait
{

    protected CacheInterface $cacher;

    /**
     * @param CacheInterface $cacher
     */
    public function setCacher(CacheInterface $cacher): void
    {
        $this->cacher = $cacher;
    }


}
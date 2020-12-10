<?php


namespace Enjoys\SimpleCache;


use Psr\SimpleCache\CacheInterface;

trait CacherTrait
{

    protected ?CacheInterface $cacher = null;

    /**
     * @param CacheInterface|null $cacher
     */
    public function setCacher(?CacheInterface $cacher): void
    {
        $this->cacher = $cacher;
    }


}
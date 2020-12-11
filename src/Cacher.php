<?php

declare(strict_types=1);

namespace Enjoys\SimpleCache;


use Enjoys\Traits\Options;
use Psr\SimpleCache\CacheInterface;

abstract class Cacher implements CacheInterface
{
    use Options;

    protected const DEFAULT_TTL = 31536000; // 1 year

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param mixed $key
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkValidKey($key): string
    {
        if (!\is_scalar($key) || \strpbrk((string)$key, '{}()/\@:') || mb_strlen((string)$key) > 64) {
            throw new InvalidArgumentException('key string is not a legal value.');
        }
        return (string)$key;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function handlingDefaultValue($value){


        if($value instanceof \Closure){
            return $value();
        }

        return $value;
    }

    /**
     * @param null|int|\DateInterval $ttl
     * @param int|null $addedTime
     * @return int
     */
    protected function normalizeTtl($ttl, ?int $addedTime = null): int
    {
        if ($ttl instanceof \DateInterval) {
            return (new \DateTime('@0'))->add($ttl)->getTimestamp();
        }
        $addedTime ??= time();
        return $addedTime + ($ttl ?? self::DEFAULT_TTL);
    }

    /**
     * @param iterable $keys
     * @return bool
     * @throws CacheException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function deleteMultiple($keys): bool
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->delete($key);
        }
        return !in_array(false, $result);
    }

}
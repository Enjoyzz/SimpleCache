<?php

declare(strict_types=1);

namespace Enjoys\SimpleCache;


use Enjoys\Traits\Options;
use Psr\SimpleCache\CacheInterface;

abstract class Cacher implements CacheInterface
{
    use Options;

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
        if (!\is_scalar($key) || \strpbrk((string)$key, '{}()/\@:')) {
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

}
<?php


namespace Enjoys\SimpleCache;


use Enjoys\SimpleCache\Drivers\DriverInterface;
use Enjoys\Traits\Options;

abstract class Driver implements DriverInterface
{
    use Options;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
        $this->init();
    }

    abstract protected function init(): void;
}
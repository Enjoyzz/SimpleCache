<?php

namespace Enjoys\SimpleCache;


class InvalidArgumentException
    extends CacheException
    implements \Psr\SimpleCache\InvalidArgumentException
{

}
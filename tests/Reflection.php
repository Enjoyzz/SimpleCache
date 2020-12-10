<?php


namespace Tests\Enjoys\SimpleCache;


trait Reflection
{
    /**
     * getPrivateMethod
     *
     * @param string $className
     * @param string $methodName
     * @return    \ReflectionMethod
     * @throws \ReflectionException
     * @author    Joe Sexton <joe@webtipblog.com>
     */
    public function getPrivateMethod(string $className, string $methodName): \ReflectionMethod
    {
        $reflector = new \ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * getPrivateProperty
     *
     * @param string $className
     * @param string $propertyName
     * @return    \ReflectionProperty
     * @throws \ReflectionException
     * @author    Joe Sexton <joe@webtipblog.com>
     */
    public function getPrivateProperty(string $className, string $propertyName): \ReflectionProperty
    {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
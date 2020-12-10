<?php


namespace Tests\Enjoys\SimpleCache;


trait Reflection
{
    /**
     * getPrivateMethod
     *
     * @author	Joe Sexton <joe@webtipblog.com>
     * @param 	string $className
     * @param 	string $methodName
     * @return	\ReflectionMethod
     */
    public function getPrivateMethod($className, $methodName)
    {
        $reflector = new \ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * getPrivateProperty
     *
     * @author	Joe Sexton <joe@webtipblog.com>
     * @param 	string $className
     * @param 	string $propertyName
     * @return	\ReflectionProperty
     */
    public function getPrivateProperty($className, $propertyName)
    {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }
}
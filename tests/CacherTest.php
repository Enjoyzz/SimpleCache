<?php


namespace Tests\Enjoys\SimpleCache;


use Enjoys\SimpleCache\Cacher;
use Enjoys\SimpleCache\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CacherTest extends TestCase
{

    public function validkeys()
    {
        return [
            ['str', 'str'],
            [0, '0'],
            [1, '1'],
            [05, '5'],
            ['05', '05'],
            [50, '50'],
            [2.5, '2.5'],
            ['Latin.Letters.With.Nums.0123456789', 'Latin.Letters.With.Nums.0123456789'],
            ['UTF8.Буквы.Сменшанные.С.цифрами.0123456789', 'UTF8.Буквы.Сменшанные.С.цифрами.0123456789'],
            ['_', '_'],
            ['#$%^&*_-=+|,', '#$%^&*_-=+|,'],
            [str_repeat("s", 64), str_repeat("s", 64)],
            [str_repeat("ф", 64), str_repeat("ф", 64)],
        ];
    }

    public function invalidkeys(){
        return [
            [':test'],
            ['/test'],
            ['te@st'],
            ['te(t'],
            ['test)'],
            ['te{st'],
            ['}test'],
            [str_repeat("s", 65)],
            [str_repeat("ф", 65)],
        ];
    }


    /**
     * @dataProvider validkeys
     */
    public function testCheckValidKey_true($key, $expect)
    {
        $mock = $this->getMockForAbstractClass(Cacher::class);
        $method = $this->getPrivateMethod(Cacher::class, 'checkValidKey');
        $this->assertSame($expect, $method->invokeArgs($mock, [$key]));
    }

    /**
     * @dataProvider invalidkeys
     */
    public function testCheckValidKey_invalid($key)
    {
        $this->expectException(InvalidArgumentException::class);
        $mock = $this->getMockForAbstractClass(Cacher::class);
        $method = $this->getPrivateMethod(Cacher::class, 'checkValidKey');
        $method->invokeArgs($mock, [$key]);
    }

    public function handledDefaultData()
    {
        $obj = new \stdClass();
        $obj->key = 'param';

        return [
            [function(){return 'test';}, 'test'],
            [fn() => 'test', 'test'],
            ['test', 'test'],
            [$obj, $obj]
        ];
    }

    /**
     * @dataProvider handledDefaultData
     */
    public function testHandlingDefaultValue($value, $expect)
    {
        $mock = $this->getMockForAbstractClass(Cacher::class);
        $method = $this->getPrivateMethod(Cacher::class, 'handlingDefaultValue');
        $this->assertSame($expect, $method->invokeArgs($mock, [$value]));
    }


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
}
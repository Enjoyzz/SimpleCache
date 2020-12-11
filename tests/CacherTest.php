<?php
/** @noinspection PhpMissingReturnTypeInspection */

/** @noinspection PhpDocSignatureInspection */


namespace Tests\Enjoys\SimpleCache;


use Enjoys\SimpleCache\Cacher;
use Enjoys\SimpleCache\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CacherTest extends TestCase
{

    use Reflection;

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

    public function invalidkeys()
    {
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
     * @throws \ReflectionException
     */
    public function testCheckValidKey_true($key, $expect)
    {
        $mock = $this->getMockForAbstractClass(Cacher::class);
        $method = $this->getPrivateMethod(Cacher::class, 'checkValidKey');
        $this->assertSame($expect, $method->invokeArgs($mock, [$key]));
    }

    /**
     * @dataProvider invalidkeys
     * @throws \ReflectionException
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
            [
                function () {
                    return 'test';
                },
                'test'
            ],
            [fn() => 'test', 'test'],
            ['test', 'test'],
            [$obj, $obj]
        ];
    }

    /**
     * @dataProvider handledDefaultData
     * @throws \ReflectionException
     */
    public function testHandlingDefaultValue($value, $expect)
    {
        $mock = $this->getMockForAbstractClass(Cacher::class);
        $method = $this->getPrivateMethod(Cacher::class, 'handlingDefaultValue');
        $this->assertSame($expect, $method->invokeArgs($mock, [$value]));
    }

    public function ttl()
    {
        return [
            [null, (new \ReflectionClass(Cacher::class))->getConstant('DEFAULT_TTL')],
            [-1, -1],
            [1, 1],
            [(new \DateInterval('P2Y4DT6H8M')), 63439680],
        ];
    }

    /**
     * @dataProvider ttl
     */
    public function testNormalizeTtl($ttl, $expect)
    {
        $mock = $this->getMockForAbstractClass(Cacher::class);
        $getTTL = $this->getPrivateMethod(Cacher::class, 'normalizeTtl');
        $result = $getTTL->invokeArgs($mock, [$ttl, 0]);
        $this->assertSame($expect, $result);
    }

}
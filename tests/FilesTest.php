<?php


namespace Tests\Enjoys\SimpleCache;


use Enjoys\SimpleCache\Drivers\File;
use PHPUnit\Framework\TestCase;

class FilesTest extends TestCase
{
    private File $cacher;

    protected function setUp(): void
    {
        $this->cacher = new File();
    }

//    protected function tearDown(): void
//    {
//        $this->cacher = null;
//    }

    public function test_1()
    {

        $this->cacher->set('1', 2);
        $this->assertSame(2, $this->cacher->get(1));
    }

    public function test_ddd()
    {

        $this->cacher->set(2, 3, 5);
        $this->assertSame(3, $this->cacher->get(2));
        sleep(4);
        $this->assertSame(3, $this->cacher->get(2));
        sleep(2);
        $this->assertSame('clear', $this->cacher->get(2, 'clear'));
    }
}
<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Php;
use Mockery as m;

class PhpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Php
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new Php();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path']
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(PHPDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}

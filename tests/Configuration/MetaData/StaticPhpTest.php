<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\StaticPhp;
use Mockery as m;

class StaticPhpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StaticPhp
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new StaticPhp();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path']
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(StaticPHPDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}

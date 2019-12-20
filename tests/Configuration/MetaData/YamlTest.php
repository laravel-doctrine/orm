<?php

use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Yaml;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    /**
     * @var Yaml
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new Yaml();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path']
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(YamlDriver::class, $resolved);
    }

    public function test_can_specify_extension_without_error()
    {
        $resolved = $this->meta->resolve([
            'paths'     => 'entities',
            'extension' => '.orm.yml'
        ]);

        $this->assertInstanceOf(YamlDriver::class, $resolved);
    }

    public function test_can_not_specify_extension_without_error()
    {
        $resolved = $this->meta->resolve([
            'paths'     => 'entities'
        ]);

        $this->assertInstanceOf(YamlDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}

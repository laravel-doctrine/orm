<?php

use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\SimplifiedYaml;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SimplifiedYamlTest extends TestCase
{
    /**
     * @var SimplifiedYaml
     */
    protected $meta;

    protected function setUp()
    {
        $this->meta = new SimplifiedYaml();
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'     => ['entities' => 'App\Entities'],
            'dev'       => true,
            'extension' => '.yaml',
            'proxies'   => ['path' => 'path']
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(SimplifiedYamlDriver::class, $resolved);
    }

    protected function tearDown()
    {
        m::close();
    }
}

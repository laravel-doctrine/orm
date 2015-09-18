<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\MetaData\Annotations;
use LaravelDoctrine\ORM\Configuration\MetaData\Config;
use Mockery as m;
use Mockery\Mock;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $config;

    /**
     * @var Annotations
     */
    protected $meta;

    protected function setUp()
    {
        $this->config = m::mock(Repository::class);
        $this->config->shouldReceive('get')->with('mappings', [])->once()->andReturn([
            'App\User' => []
        ]);

        $this->meta = new Config($this->config);
    }

    public function test_can_resolve()
    {
        $resolved = $this->meta->resolve([
            'paths'        => ['entities'],
            'dev'          => true,
            'proxies'      => ['path' => 'path'],
            'mapping_file' => 'mappings'
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
    }
}

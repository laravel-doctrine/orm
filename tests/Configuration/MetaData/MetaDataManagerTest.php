<?php

use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\MetaData\Annotations;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Configuration\MetaData\Yaml;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;
use Mockery as m;

class MetaDataManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MetaDataManager
     */
    protected $manager;

    /**
     * @var Container
     */
    protected $app;

    protected function setUp()
    {
        $this->app = m::mock(Container::class);
        $this->app->shouldReceive('make')->andReturn(m::self());

        $this->manager = new MetaDataManager(
            $this->app
        );
    }

    public function test_driver_returns_the_default_driver()
    {
        $this->app->shouldReceive('resolve')->andReturn(new Annotations());

        $this->assertInstanceOf(Annotations::class, $this->manager->driver());
    }

    public function test_driver_can_return_a_given_driver()
    {
        $this->app->shouldReceive('resolve')->andReturn(new Yaml());

        $this->assertInstanceOf(Yaml::class, $this->manager->driver('yaml'));
    }

    public function test_cant_resolve_unsupported_drivers()
    {
        $this->setExpectedException(DriverNotFound::class);
        $this->manager->driver('non-existing');
    }

    public function test_can_create_custom_drivers()
    {
        $this->manager->extend('new', function () {
            return 'configuration';
        });

        $this->assertEquals('configuration', $this->manager->driver('new'));
    }

    public function test_can_use_application_when_extending()
    {
        $this->manager->extend('new', function ($app) {
            $this->assertInstanceOf(Container::class, $app);
        });
    }

    public function test_can_replace_an_existing_driver()
    {
        $this->manager->extend('annotations', function () {
            return 'configuration';
        });

        $this->assertEquals('configuration', $this->manager->driver('annotations'));
    }

    protected function tearDown()
    {
        m::close();
    }
}

<?php

use Brouwers\LaravelDoctrine\Configuration\MetaData\AbstractMetaData;
use Brouwers\LaravelDoctrine\Configuration\MetaData\Annotations;
use Brouwers\LaravelDoctrine\Configuration\MetaData\MetaDataManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Tools\Setup;

class MetaDataManagerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        MetaDataManager::registerDrivers([
            'annotations' => [
                'paths' => []
            ]
        ], true);
    }

    public function test_register_metadatas()
    {
        $drivers = MetaDataManager::getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertInstanceOf(Annotations::class, head($drivers));
    }

    public function test_metadata_can_be_extended()
    {
        MetaDataManager::extend('annotations', function ($driver) {

            // Should give instance of the already registered driver
            $this->assertInstanceOf(Configuration::class, $driver);

            return $driver;
        });

        $driver = MetaDataManager::resolve('annotations');

        $this->assertInstanceOf(Configuration::class, $driver);
    }

    public function test_custom_metadata_can_be_set()
    {
        MetaDataManager::extend('custom', function () {
            return Setup::createAnnotationMetadataConfiguration([], true);
        });

        $driver = MetaDataManager::resolve('custom');
        $this->assertInstanceOf(Configuration::class, $driver);
    }

    public function test_a_string_class_can_be_use_as_extend()
    {
        MetaDataManager::extend('custom3', StubMetaData::class);

        $driver = MetaDataManager::resolve('custom3');
        $this->assertEquals('stub', $driver);
    }
}

function config()
{
    return null;
}

function event()
{
    return null;
}

function app()
{
    return null;
}

class StubMetaData extends AbstractMetaData
{
    /**
     * @return mixed
     */
    public function resolve()
    {
        return 'stub';
    }

    /**
     * @param array $settings
     * @param bool  $dev
     *
     * @return static
     */
    public function configure(array $settings = [], $dev = false)
    {
        return $this;
    }
}

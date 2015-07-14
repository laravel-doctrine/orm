<?php

use Brouwers\LaravelDoctrine\Configuration\Connections\AbstractConnection;
use Brouwers\LaravelDoctrine\Configuration\Connections\ConnectionManager;
use Brouwers\LaravelDoctrine\Configuration\Connections\MysqlConnection;

class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        ConnectionManager::registerConnections([
            'mysql' => [
                'host'     => 'host',
                'database' => 'database',
                'username' => 'username',
                'password' => 'password',
                'charset'  => 'charset',
            ]
        ]);
    }

    public function test_register_connections()
    {
        $drivers = ConnectionManager::getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertInstanceOf(MysqlConnection::class, head($drivers));
    }

    public function test_connection_can_be_extended()
    {
        ConnectionManager::extend('mysql', function ($driver) {

            // Should give instance of the already registered driver
            $this->assertTrue(is_array($driver));

            return [
                'host'     => 'host',
                'database' => 'database',
                'username' => 'username2',
                'password' => 'password',
                'charset'  => 'charset',
            ];
        });

        $driver = ConnectionManager::resolve('mysql');

        $this->assertEquals('username2', $driver['username']);
    }

    public function test_custom_connection_can_be_set()
    {
        ConnectionManager::extend('custom', function () {
            return [
                'host'     => 'host',
                'database' => 'database',
                'username' => 'username3',
                'password' => 'password',
                'charset'  => 'charset',
            ];
        });

        $driver = ConnectionManager::resolve('custom');
        $this->assertEquals('username3', $driver['username']);
    }

    public function test_a_string_class_can_be_use_as_extend()
    {
        ConnectionManager::extend('custom3', StubConnection::class);

        $driver = ConnectionManager::resolve('custom3');
        $this->assertContains('stub', $driver);
    }
}

class StubConnection extends AbstractConnection
{
    /**
     * @param array $config
     *
     * @return array
     */
    public function configure($config = [])
    {
        $this->settings = ['stub' => 'stub'];

        return $this;
    }
}

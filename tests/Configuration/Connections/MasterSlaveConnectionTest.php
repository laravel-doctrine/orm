<?php

use Doctrine\DBAL\Connections\MasterSlaveConnection as MasterSlaveDoctrineWrapper;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\MasterSlaveConnection;
use Mockery as m;

/**
 * Basic unit tests for master slave connection.
 */
class MasterSlaveConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testMasterSlaveConnection.
     *
     * @return array
     */
    public function getMasterSlaveConnectionData()
    {
        $out = [];

        $dummyInputConfig = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'test',
            'username'  => 'homestead',
            'password'  => 'secret',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
            'write'     => [
                'port'      => 3307,
                'user'      => 'homestead1',
                'password'  => 'secret1',
            ],
            'read' => [
                [
                    'port'     => 3308,
                    'database' => 'test2',
                ],
                [
                    'host' => 'localhost2',
                    'port' => 3309
                ],
            ],
        ];

        $dummyExpectedConfig = [
            'wrapperClass' => MasterSlaveDoctrineWrapper::class,
            'driver'       => 'pdo_mysql',
            'slaves'       => [
                [
                    'host'        => 'localhost',
                    'user'        => 'homestead',
                    'password'    => 'secret',
                    'dbname'      => 'test2',
                    'port'        => '3308',
                    'charset'     => 'charset',
                    'unix_socket' => 'unix_socket',
                    'prefix'      => 'prefix'
                ],
                [
                    'host'        => 'localhost2',
                    'user'        => 'homestead',
                    'password'    => 'secret',
                    'dbname'      => 'test',
                    'port'        => '3309',
                    'charset'     => 'charset',
                    'unix_socket' => 'unix_socket',
                    'prefix'      => 'prefix'
                ]
            ],
            'master' => [
                'host'        => 'localhost',
                'user'        => 'homestead1',
                'password'    => 'secret1',
                'dbname'      => 'test',
                'port'        => '3307',
                'charset'     => 'charset',
                'unix_socket' => 'unix_socket',
                'prefix'      => 'prefix'
            ],
        ];

        // Case #0. Simple valid configuration with mysql base settings.
        $resolvedBaseSettings = [
            'driver'      => 'pdo_mysql',
            'host'        => 'localhost',
            'dbname'      => 'test',
            'user'        => 'homestead',
            'password'    => 'secret',
            'charset'     => 'charset',
            'port'        => 'port',
            'unix_socket' => 'unix_socket',
            'prefix'      => 'prefix'
        ];
        $out[] = [$resolvedBaseSettings, $dummyInputConfig, $dummyExpectedConfig];

        // Case #1. Configuration is only set in the read/wriet nodes.
        $resolvedBaseSettings = [
            'driver'      => 'pdo_mysql',
        ];

        $expectedConfig = [
            'wrapperClass' => MasterSlaveDoctrineWrapper::class,
            'driver'       => 'pdo_mysql',
            'slaves'       => [
                [
                    'host'     => 'localhost',
                    'user'     => 'homestead',
                    'password' => 'secret',
                    'dbname'   => 'test2',
                    'port'     => '3308',
                ],
                [
                    'host'     => 'localhost2',
                    'user'     => 'homestead',
                    'password' => 'secret',
                    'dbname'   => 'test',
                    'port'     => '3309',
                ]
            ],
            'master' => [
                'host'     => 'localhost',
                'user'     => 'homestead',
                'password' => 'secret1',
                'dbname'   => 'test',
                'port'     => '3307',
            ],
        ];

        $inputConfig = [
            'write' => [
                'port'     => 3307,
                'password' => 'secret1',
                'host'     => 'localhost',
                'database' => 'test',
                'username' => 'homestead'
            ],
            'read' => [
                [
                    'port'     => 3308,
                    'database' => 'test2',
                    'host'     => 'localhost',
                    'username' => 'homestead',
                    'password' => 'secret'
                ],
                [
                    'host'     => 'localhost2',
                    'port'     => 3309,
                    'database' => 'test',
                    'username' => 'homestead',
                    'password' => 'secret'
                ],
            ],
        ];
        $out[] = [$resolvedBaseSettings, $inputConfig, $expectedConfig];

        // Case #2. Simple valid configuration with oracle base settings.
        $expectedConfigOracle                   = $expectedConfig;
        $expectedConfigOracle['driver']         = 'oci8';
        $expectedConfigOracle['master']['user'] = 'homestead1';

        $resolvedBaseSettings = [
            'driver'      => 'oci8',
            'host'        => 'localhost',
            'dbname'      => 'test',
            'user'        => 'homestead',
            'password'    => 'secret',
            'port'        => 'port',
        ];
        $out[] = [$resolvedBaseSettings, $dummyInputConfig, $expectedConfigOracle];

        // Case #3. Simple valid configuration with pgqsql base settings.
        $expectedConfigPgsql                         = $expectedConfig;
        $expectedConfigPgsql['driver']               = 'pgsql';
        $expectedConfigPgsql['master']['user']       = 'homestead1';
        $expectedConfigPgsql['master']['sslmode']    = 'sslmode';
        $expectedConfigPgsql['slaves'][0]['sslmode'] = 'sslmode';
        $expectedConfigPgsql['slaves'][1]['sslmode'] = 'sslmode';

        $resolvedBaseSettings = [
            'driver'      => 'pgsql',
            'host'        => 'localhost',
            'dbname'      => 'test',
            'user'        => 'homestead',
            'password'    => 'secret',
            'port'        => 'port',
            'sslmode'     => 'sslmode',
        ];
        $out[] = [$resolvedBaseSettings, $dummyInputConfig, $expectedConfigPgsql];

        // Case #4. Simple valid configuration with sqlite base settings.
        $inputConfigSqlite = $dummyInputConfig;
        unset($inputConfigSqlite['read'][0]['database']);
        unset($inputConfigSqlite['read'][1]['database']);
        unset($inputConfigSqlite['write']['database']);

        $expectedConfigSqlite = [
            'wrapperClass' => MasterSlaveDoctrineWrapper::class,
            'driver'       => 'pdo_sqlite',
            'slaves'       => [
                [
                    'user'     => 'homestead',
                    'password' => 'secret',
                    'port'     => 3308,
                    'path'     => ':memory',
                    'memory'   => true,
                ],
                [
                    'host'     => 'localhost2',
                    'user'     => 'homestead',
                    'password' => 'secret',
                    'port'     => 3309,
                    'path'     => ':memory',
                    'memory'   => true,
                ]
            ],
            'master' => [
                'user'     => 'homestead1',
                'password' => 'secret1',
                'port'     => 3307,
                'memory'   => true,
                'path'     => ':memory',
            ],
        ];

        $resolvedBaseSettings = [
            'driver'   => 'pdo_sqlite',
            'path'     => ':memory',
            'user'     => 'homestead',
            'password' => 'secret',
            'memory'   => true
        ];
        $out[] = [$resolvedBaseSettings, $inputConfigSqlite, $expectedConfigSqlite];

        return $out;
    }

    /**
     * Check if master slave connection manages configuration well.
     *
     * @param array $resolvedBaseSettings
     * @param array $settings
     * @param $expectedOutput
     *
     * @dataProvider getMasterSlaveConnectionData
     */
    public function testMasterSlaveConnection(array $resolvedBaseSettings, array $settings, array $expectedOutput)
    {
        $this->assertEquals(
            $expectedOutput,
            (new MasterSlaveConnection(m::mock(Repository::class), $resolvedBaseSettings))->resolve($settings)
        );
    }
}

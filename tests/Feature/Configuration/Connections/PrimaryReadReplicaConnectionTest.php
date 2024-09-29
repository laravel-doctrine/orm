<?php

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection as PrimaryReadReplicaDoctrineWrapper;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Connections\PrimaryReadReplicaConnection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Basic unit tests for primary read-replica connection
 */
class PrimaryReadReplicaConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(PrimaryReadReplicaDoctrineWrapper::class)) {
            $this->markTestSkipped('Skipped for doctrine/dbal < 2.11');
        }
    }

    /**
     * Data provider for testPrimaryReplicaConnection.
     *
     * @return array
     */
    public function getPrimaryReplicaConnectionData()
    {
        $out = [];

        // Case #0. Simple valid configuration with mysql base settings.
        $out[] = [
            $this->getResolvedMysqlConfig(),
            $this->getInputConfigwithArrayOfReplicasInReadKey(),
            $this->getExpectedConfig()
        ];

        // Case #1. Configuration is only set in the read/write nodes.
        $out[] = [
            ['driver' => 'pdo_mysql'],
            $this->getNodesInputConfig(),
            $this->getNodesExpectedConfig()
        ];

        // Case #2. Simple valid configuration with oracle base settings.
        $out[] = [
            $this->getResolvedOracleConfig(),
            $this->getInputConfigwithArrayOfReplicasInReadKey(),
            $this->getOracleExpectedConfig()
        ];

        // Case #3. Simple valid configuration with pgqsql base settings.
        $out[] = [
            $this->getResolvedPgqsqlConfig(),
            $this->getInputConfigwithArrayOfReplicasInReadKey(),
            $this->getPgsqlExpectedConfig()
        ];

        // Case #4. Simple valid configuration with sqlite base settings.
        $out[] = [
            $this->getResolvedSqliteConfig(),
            $this->getSqliteInputConfig(),
            $this->getSqliteExpectedConfig()
        ];

        // Case #5. Valid configuration as with 1 replica 'read' entry and plain-text host
        $out[] = [
            $this->getResolvedMysqlConfig(),
            $this->getInputConfigWithPlainTextHostValue(),
            $this->getExpectedConfigForCase5()
        ];

        // Case #6. Valid configuration as with 1 replica config 'read' entry and array of hosts in 'host' key
        $out[] = [
            $this->getResolvedMysqlConfig(),
            $this->getInputConfigWithArrayAsHostValue(),
            $this->getExpectedConfigForCase6()
        ];

        return $out;
    }

    /**
     * Check if primary replica connection manages configuration well.
     *
     * @param array $resolvedBaseSettings
     * @param array $settings
     * @param $expectedOutput
     *
     * @dataProvider getPrimaryReplicaConnectionData
     */
    public function testPrimaryReplicaConnection(array $resolvedBaseSettings, array $settings, array $expectedOutput)
    {
        $this->assertEquals(
            $expectedOutput,
            (new PrimaryReadReplicaConnection(m::mock(Repository::class), $resolvedBaseSettings))->resolve($settings)
        );
    }

    /**
     * Returns dummy input configuration for testing.
     */
    private function getInputConfigwithArrayOfReplicasInReadKey(): array
    {
        return [
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
            'serverVersion'       => '5.8',
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]
        ];
    }

    private function getInputConfigWithPlainTextHostValue()
    {
        return [
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
                'port'     => 3308,
                'database' => 'test2',
                'host'     => 'newhost',
            ],
            'serverVersion'       => '5.8',
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]
        ];
    }

    private function getInputConfigWithArrayAsHostValue()
    {
        return [
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
                'port'     => 3308,
                'database' => 'test2',
                'host'     => ['newhost1', 'newhost2'],
            ],
            'serverVersion'       => '5.8',
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]
        ];
    }

    /**
     * Returns dummy expected result configuration for testing.
     *
     * @return array
     */
    private function getExpectedConfig()
    {
        return [
            'wrapperClass'   => PrimaryReadReplicaDoctrineWrapper::class,
            'driver'         => 'pdo_mysql',
            'serverVersion'  => '5.8',
            'replica'        => [
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
            'primary' => [
                'host'        => 'localhost',
                'user'        => 'homestead1',
                'password'    => 'secret1',
                'dbname'      => 'test',
                'port'        => '3307',
                'charset'     => 'charset',
                'unix_socket' => 'unix_socket',
                'prefix'      => 'prefix'
            ],
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ],
        ];
    }

    /**
     * Returns dummy expected result configuration for testing.
     */
    private function getExpectedConfigForCase5(): array
    {
        return [
            'wrapperClass'   => PrimaryReadReplicaDoctrineWrapper::class,
            'driver'         => 'pdo_mysql',
            'serverVersion'  => '5.8',
            'replica'        => [
                [
                    'host'        => 'newhost',
                    'user'        => 'homestead',
                    'password'    => 'secret',
                    'dbname'      => 'test2',
                    'port'        => '3308',
                    'charset'     => 'charset',
                    'unix_socket' => 'unix_socket',
                    'prefix'      => 'prefix'
                ],
            ],
            'primary' => [
                'host'        => 'localhost',
                'user'        => 'homestead1',
                'password'    => 'secret1',
                'dbname'      => 'test',
                'port'        => '3307',
                'charset'     => 'charset',
                'unix_socket' => 'unix_socket',
                'prefix'      => 'prefix'
            ],
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ],
        ];
    }

    /**
     * Returns dummy expected result configuration for testing.
     */
    private function getExpectedConfigForCase6(): array
    {
        return [
            'wrapperClass'   => PrimaryReadReplicaDoctrineWrapper::class,
            'driver'         => 'pdo_mysql',
            'serverVersion'  => '5.8',
            'replica'        => [
                [
                    'host'        => 'newhost1',
                    'user'        => 'homestead',
                    'password'    => 'secret',
                    'dbname'      => 'test2',
                    'port'        => '3308',
                    'charset'     => 'charset',
                    'unix_socket' => 'unix_socket',
                    'prefix'      => 'prefix'
                ],
                [
                    'host'        => 'newhost2',
                    'user'        => 'homestead',
                    'password'    => 'secret',
                    'dbname'      => 'test2',
                    'port'        => '3308',
                    'charset'     => 'charset',
                    'unix_socket' => 'unix_socket',
                    'prefix'      => 'prefix'
                ],
            ],
            'primary' => [
                'host'        => 'localhost',
                'user'        => 'homestead1',
                'password'    => 'secret1',
                'dbname'      => 'test',
                'port'        => '3307',
                'charset'     => 'charset',
                'unix_socket' => 'unix_socket',
                'prefix'      => 'prefix'
            ],
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ],
        ];
    }

    /**
     * Returns dummy input configuration where configuration is only set in read and write nodes.
     *
     * @return array
     */
    private function getNodesInputConfig()
    {
        return [
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
    }

    /**
     * Returns dummy expected output configuration where configuration is only set in read and write nodes.
     *
     * @return array
     */
    private function getNodesExpectedConfig()
    {
        return [
            'wrapperClass'  => PrimaryReadReplicaDoctrineWrapper::class,
            'driver'        => 'pdo_mysql',
            'replica'       => [
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
            'primary' => [
                'host'     => 'localhost',
                'user'     => 'homestead',
                'password' => 'secret1',
                'dbname'   => 'test',
                'port'     => '3307',
            ],
        ];
    }

    /**
     * Returns dummy expected result configuration for testing oracle connections.
     *
     * @return array
     */
    private function getOracleExpectedConfig()
    {
        $expectedConfigOracle                    = $this->getNodesExpectedConfig();
        $expectedConfigOracle['driver']          = 'oci8';
        $expectedConfigOracle['primary']['user'] = 'homestead1';
        $expectedConfigOracle['serverVersion']   = '5.8';

        $expectedConfigOracle['defaultTableOptions'] = [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        ];

        return $expectedConfigOracle;
    }

    /**
     * Returns dummy expected result configuration for testing pgsql connections.
     *
     * @return array
     */
    private function getPgsqlExpectedConfig()
    {
        $expectedConfigPgsql                          = $this->getNodesExpectedConfig();
        $expectedConfigPgsql['driver']                = 'pgsql';
        $expectedConfigPgsql['primary']['user']       = 'homestead1';
        $expectedConfigPgsql['primary']['sslmode']    = 'sslmode';
        $expectedConfigPgsql['replica'][0]['sslmode'] = 'sslmode';
        $expectedConfigPgsql['replica'][1]['sslmode'] = 'sslmode';
        $expectedConfigPgsql['serverVersion']         = '5.8';

        $expectedConfigPgsql['defaultTableOptions'] = [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        ];

        return $expectedConfigPgsql;
    }

    /**
     * Returns dummy expected result configuration for testing Sqlite connections.
     *
     * @return array
     */
    private function getSqliteExpectedConfig()
    {
        return [
            'wrapperClass'  => PrimaryReadReplicaDoctrineWrapper::class,
            'driver'        => 'pdo_sqlite',
            'replica'       => [
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
            'primary' => [
                'user'     => 'homestead1',
                'password' => 'secret1',
                'port'     => 3307,
                'memory'   => true,
                'path'     => ':memory',
            ],
            'serverVersion'       => '5.8',
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ]
        ];
    }

    /**
     * Returns dummy input configuration for testing Sqlite connections.
     *
     * @return array
     */
    private function getSqliteInputConfig()
    {
        $inputConfigSqlite = $this->getInputConfigwithArrayOfReplicasInReadKey();
        unset($inputConfigSqlite['read'][0]['database']);
        unset($inputConfigSqlite['read'][1]['database']);
        unset($inputConfigSqlite['write']['database']);

        return $inputConfigSqlite;
    }

    /**
     * Returns already resolved mysql configuration.
     *
     * @return array
     */
    private function getResolvedMysqlConfig()
    {
        return [
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
    }

    /**
     * Returns already resolved oci configuration.
     *
     * @return array
     */
    private function getResolvedOracleConfig()
    {
        return [
            'driver'      => 'oci8',
            'host'        => 'localhost',
            'dbname'      => 'test',
            'user'        => 'homestead',
            'password'    => 'secret',
            'port'        => 'port',
        ];
    }

    /**
     * Returns already resolved sqlite configuration.
     *
     * @return array
     */
    private function getResolvedSqliteConfig()
    {
        return [
            'driver'   => 'pdo_sqlite',
            'path'     => ':memory',
            'user'     => 'homestead',
            'password' => 'secret',
            'memory'   => true
        ];
    }

    /**
     * Returns already resolved pgsql configuration.
     *
     * @return array
     */
    private function getResolvedPgqsqlConfig()
    {
        return [
            'driver'      => 'pgsql',
            'host'        => 'localhost',
            'dbname'      => 'test',
            'user'        => 'homestead',
            'password'    => 'secret',
            'port'        => 'port',
            'sslmode'     => 'sslmode',
        ];
    }
}

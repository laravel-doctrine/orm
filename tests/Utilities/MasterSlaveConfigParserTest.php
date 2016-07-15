<?php

use LaravelDoctrine\ORM\Utilities\MasterSlaveConfigParser;

class MasterSlaveConfigParserTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider validConfigProvider
     *
     * @param array $validConfig
     */
    public function test_HasValidConfig_returns_true_on_valid_config(array $validConfig)
    {
        $this->assertTrue(MasterSlaveConfigParser::hasValidConfig($validConfig));
    }

    /**
     * @dataProvider invalidConfigProvider
     *
     * @param array $invalidConfig
     */
    public function test_HasValidConfig_returns_false_on_missing_config(array $invalidConfig)
    {
        $this->assertFalse(MasterSlaveConfigParser::hasValidConfig($invalidConfig));
    }

    /**
     * @return array
     */
    public function validConfigProvider()
    {
        $valid1['config']        = array_merge(
            $this->getDatabaseConfigTemplate(),
            [
                'write' => [
                    'host' => '192.168.0.1',
                ],
                'read'  => [
                    [
                        'host'     => '192.168.0.2',
                        'username' => 'read_user',
                    ],
                ],
            ]
        );
        $valid1['expectedWrite'] = [
            'user'     => 'write_db_username',
            'password' => 'write_db_password',
            'host'     => '192.168.0.1',  //Override the parent's localhost
            'dbname'   => 'write_db_name',
            'port'     => 3306,
        ];
        $valid1['expectedRead']  = [
            [
                'user'     => 'read_user',
                'password' => 'write_db_password',
                'host'     => '192.168.0.2',  //Override the parent's localhost
                'dbname'   => 'write_db_name',
                'port'     => 3306,
            ],
        ];

        $valid2['config']        = array_merge(
            $this->getDatabaseConfigTemplate(),
            [
                'read' => [
                    [
                        'host' => '192.168.0.1',
                    ],
                ]
            ]
        );
        $valid2['expectedWrite'] = [
            'user'     => 'write_db_username',
            'password' => 'write_db_password',
            'host'     => 'localhost',
            'dbname'   => 'write_db_name',
            'port'     => 3306,
        ];
        $valid2['expectedRead']  = [
            [
                'user'     => 'write_db_username',
                'password' => 'write_db_password',
                'host'     => '192.168.0.1',  //Override the parent's localhost
                'dbname'   => 'write_db_name',
                'port'     => 3306,
            ],
        ];

        return [
            [$valid1['config'], $valid1['expectedWrite'], $valid1['expectedRead']],
            [$valid2['config'], $valid2['expectedWrite'], $valid2['expectedRead']],
        ];
    }

    /**
     * @return array
     */
    public function getDatabaseConfigTemplate()
    {
        return [
            'host'      => 'localhost',
            'database'  => 'write_db_name',
            'username'  => 'write_db_username',
            'password'  => 'write_db_password',
            'driver'    => 'mysql',
            'port'      => 3306,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
        ];
    }

    /**
     * @return array
     */
    public function invalidConfigProvider()
    {
        $invalidConfig1 = [];
        $invalidConfig2 = array_merge(
            $this->getDatabaseConfigTemplate(),
            [
                'write' => [],
//                'read'  => [], //Read is missing, it's invalid
            ]
        );
        $invalidConfig3 = array_merge(
            $this->getDatabaseConfigTemplate(),
            [
                'write' => [],
                'read'  => ['host' => '123'], //Read should be an array
            ]
        );

        return [
            [$invalidConfig1],
            [$invalidConfig2],
            [$invalidConfig3],
        ];
    }

    /**
     * @dataProvider invalidConfigProvider
     *
     * @expectedException UnexpectedValueException
     *
     * @param array $invalidConfig
     */
    public function test_ParseConfig_throws_exception_on_invalid_config(array $invalidConfig)
    {
        MasterSlaveConfigParser::parseConfig($invalidConfig);
    }

    /**
     * @dataProvider validConfigProvider
     *
     * @param array $config
     * @param array $expectedWrite
     * @param array $expectedRead
     */
    public function test_ParseConfig_can_inherit_settings(
        array $config,
        array $expectedWrite,
        array $expectedRead
    ) {
        $readWriteConfig = MasterSlaveConfigParser::parseConfig($config);

        $this->assertEquals($expectedRead, $readWriteConfig['read']);
        $this->assertEquals($expectedWrite, $readWriteConfig['write']);
    }
}

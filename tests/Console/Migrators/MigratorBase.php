<?php

if (!function_exists('storage_path')) {
    function storage_path($path = null)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../../../tests/Stubs/storage';
    }
}
if (!function_exists('env')) {
    function env($var, $default = null)
    {
        return $default;
    }
}
if (!function_exists('config')) {
    function config($var)
    {
        return $var;
    }
}
if (!function_exists('app_path')) {
    function app_path($path = null)
    {
        return __DIR__ . $path;
    }
}

abstract class MigratorBase extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (file_exists(__DIR__ . '/../../Stubs/storage/doctrine.generated.php')) {
            unlink(__DIR__ . '/../../Stubs/storage/doctrine.generated.php');
        }
    }

    protected function sanityCheck()
    {
        //make sure file was generated
        $this->assertFileExists(__DIR__ . '/../../Stubs/storage/doctrine.generated.php');

        $generatedConfig = include __DIR__ . '/../../Stubs/storage/doctrine.generated.php';

        //assert at least one manager is present
        $this->assertArrayHasKey('managers', $generatedConfig);
        $this->assertTrue(count($generatedConfig['managers']) > 0);
    }
}

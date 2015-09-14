<?php

use Mockery as m;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;


class AtrauzziMigratorTest extends PHPUnit_Framework_TestCase
{
    public function test_convert_atrauzzi_config(){

        $mitchellMigrator = new \LaravelDoctrine\ORM\Console\ConvertConfigCommand();
        $application = new Application();
        $application->add($mitchellMigrator);

        $command = $application->find('doctrine:config:convert');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'author' => 'atrauzzi',
            '--source-file' => realpath(__DIR__ . '/../../Stubs/atrauzzi-config-sample.php'),
            '--dest-path' => realpath(__DIR__ . '/../../Stubs/storage')
        ]);
    }
}
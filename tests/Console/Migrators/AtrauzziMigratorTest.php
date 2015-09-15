<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AtrauzziMigratorTest extends MigratorBase
{
    public function test_convert_atrauzzi_config()
    {
        $mitchellMigrator = new \LaravelDoctrine\ORM\Console\ConvertConfigCommand();
        $application      = new Application();
        $application->add($mitchellMigrator);

        $command = $application->find('doctrine:config:convert');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'       => $command->getName(),
            'author'        => 'atrauzzi',
            '--source-file' => realpath(__DIR__ . '/../../Stubs/atrauzzi-config-sample.php'),
            '--dest-path'   => realpath(__DIR__ . '/../../Stubs/storage')
        ]);

        $this->sanityCheck();
    }
}

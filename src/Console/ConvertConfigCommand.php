<?php

namespace LaravelDoctrine\ORM\Console;

use Illuminate\Contracts\View\Factory;
use InvalidArgumentException;
use LaravelDoctrine\ORM\ConfigMigrations\MitchellMigrator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ConvertConfigCommand extends Command
{
    protected $name = 'doctrine:config:convert';

    protected $description = 'Convert the configuration file for another laravel-doctrine implementation into a valid configuration for LaravelDoctrine\ORM.';

    public function fire(Factory $viewFactory)
    {
        //add config templates directory to view locations
        $viewFactory->addLocation(realpath(__DIR__ . '/ConfigMigrations/templates'));

        if (($destPath = $this->option('dest-path')) === null) {
            $destPath = 'config';
        }

        if (($author = $this->argument('author')) === null) {
            throw new InvalidArgumentException('Missing author option');
        }

        if (($sourceFilePath = $this->option('source-file')) === null) {
            $sourceFilePath = 'config/doctrine.php';
        }

        $destPath = realpath($destPath);

        if (!is_dir($destPath)) {
            mkdir($destPath, 0777, true);
        }

        if (!is_writable($destPath)) {
            throw new InvalidArgumentException(
                sprintf("Configuration destination directory '<info>%s</info>' does not have write permissions.",
                    $destPath)
            );
        }

        $destFilePath = $destPath . '/doctrine.generated.php';

        $originalSourceFilePath = $sourceFilePath;
        $sourceFilePath = realPath($sourceFilePath);

        if (!file_exists($sourceFilePath)) {
            throw new InvalidArgumentException(
                sprintf("Source file at path '<info>%s</info>' does not exist.",
                    $originalSourceFilePath)
            );
        }

        $sourceArrayConfig = include $sourceFilePath;

        //TODO make this relative

        switch ($author) {
            case 'atrauzzi':
                //$convertedConfigString = $this->convertAtrauzzi($sourceArrayConfig, $viewFactory);
                break;
            case 'mitchellvanw':
                $convertedConfigString = $this->convertMitchell($sourceArrayConfig, $viewFactory);
                break;
            default:
                throw new InvalidArgumentException('Author provided was not a valid choice.');
        }

        file_put_contents($destFilePath, '<?php ' . $convertedConfigString);
        $this->info('Conversion successful. File generated at ' . $destFilePath);
    }

    private function convertMitchell($sourceConfig, $viewFactory)
    {
        $mMigrator = new MitchellMigrator($viewFactory);

        return $mMigrator->convertConfiguration($sourceConfig);
    }

    private function convertAtrauzzi($sourceConfig, $viewFactory)
    {
        //TODO
    }

    public function getArguments()
    {
        return [
            ['author', InputArgument::REQUIRED, 'The name of the author of the repository being migrated from. Options are "atrauzzi" and "mitchellvanw"'],

        ];
    }

    protected function getOptions()
    {
        return [
            ['dest-path', null, InputOption::VALUE_OPTIONAL, 'Where the generated configuration should be placed. Default is config.', 'config'],
            ['source-file', null, InputOption::VALUE_OPTIONAL, 'Where the source configuration file is located. Default is config/doctrine.php', 'config/doctrine.php']
        ];
    }
}

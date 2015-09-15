<?php

namespace LaravelDoctrine\ORM\Console;

use Illuminate\Container\Container as Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem as Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Console\ConfigMigrations\AtrauzziMigrator;
use LaravelDoctrine\ORM\Console\ConfigMigrations\MitchellMigrator;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertConfigCommand extends SymfonyCommand
{
    /**
     * @var string
     */
    protected $name = 'doctrine:config:convert';

    /**
     * @var string
     */
    protected $description = 'Convert the configuration file for another laravel-doctrine implementation into a valid configuration for LaravelDoctrine\ORM.';

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('doctrine:config:convert')
             ->setAliases(['doctrine:config:convert'])
             ->setDescription('Convert the configuration file for another laravel-doctrine implementation into a valid configuration for LaravelDoctrine\ORM')
             ->setDefinition([
                 new InputArgument('author', InputArgument::REQUIRED,
                     'The name of the author of the repository being migrated from. Options are "atrauzzi" and "mitchellvanw"'),
                 new InputOption('dest-path', null, InputOption::VALUE_OPTIONAL,
                     'Where the generated configuration should be placed', 'config'),
                 new InputOption('source-file', null, InputOption::VALUE_OPTIONAL,
                     'Where the source configuration file is located.', 'config/doctrine.php')
             ]);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        //add config templates directory to view locations
        //$viewFactory->addLocation(realpath(__DIR__ . '/ConfigMigrations/templates'));

        if (($destPath = $input->getOption('dest-path')) === null) {
            $destPath = 'config';
        }

        if (($author = $input->getArgument('author')) === null) {
            throw new InvalidArgumentException('Missing author option');
        }

        if (($sourceFilePath = $input->getOption('source-file')) === null) {
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
        $sourceFilePath         = realPath($sourceFilePath);

        if (!file_exists($sourceFilePath)) {
            throw new InvalidArgumentException(
                sprintf("Source file at path '<info>%s</info>' does not exist.",
                    $originalSourceFilePath)
            );
        }

        $sourceArrayConfig = include $sourceFilePath;

        $viewFactory = $this->createViewFactory();

        //TODO make this relative

        switch ($author) {
            case 'atrauzzi':
                $convertedConfigString = $this->convertAtrauzzi($sourceArrayConfig, $viewFactory);
                break;
            case 'mitchellvanw':
                $convertedConfigString = $this->convertMitchell($sourceArrayConfig, $viewFactory);
                break;
            default:
                throw new InvalidArgumentException('Author provided was not a valid choice.');
        }

        file_put_contents($destFilePath, '<?php ' . $convertedConfigString);
        $output->writeln('Conversion successful. File generated at ' . $destFilePath);
    }

    /**
     * Convert a configuration file from mitchellvanw/laravel-doctrine to a string representation of a php array configuration for this project
     *
     * @param array   $sourceConfig
     * @param Factory $viewFactory
     *
     * @return string
     */
    private function convertMitchell($sourceConfig, $viewFactory)
    {
        $mMigrator = new MitchellMigrator($viewFactory);

        return $mMigrator->convertConfiguration($sourceConfig);
    }

    /**
     * Convert a configuration file from atrauzzi/laravel-doctrine to a string representation of a php array configuration for this project
     *
     * @param array   $sourceConfig
     * @param Factory $viewFactory
     *
     * @return string
     */
    private function convertAtrauzzi($sourceConfig, $viewFactory)
    {
        $aMigrator = new AtrauzziMigrator($viewFactory);

        return $aMigrator->convertConfiguration($sourceConfig);
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return [
            [
                'author',
                InputArgument::REQUIRED,
                'The name of the author of the repository being migrated from. Options are "atrauzzi" and "mitchellvanw"'
            ],

        ];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'dest-path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Where the generated configuration should be placed. Default is config.',
                'config'
            ],
            [
                'source-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Where the source configuration file is located. Default is config/doctrine.php',
                'config/doctrine.php'
            ]
        ];
    }

    /**
     * @return \Illuminate\View\Factory
     */
    protected function createViewFactory()
    {
        $FileViewFinder = new FileViewFinder(
            new Filesystem,
            [realpath(__DIR__ . '/ConfigMigrations/templates')]
        );

        $dispatcher = new Dispatcher(new Container);

        $compiler       = new BladeCompiler(new Filesystem(), storage_path() . '/framework/views');
        $bladeEngine    = new CompilerEngine($compiler);
        $engineResolver = new EngineResolver();
        $engineResolver->register('blade', function () use (&$bladeEngine) {
            return $bladeEngine;
        });

        $viewFactory = new \Illuminate\View\Factory($engineResolver, $FileViewFinder, $dispatcher);

        return $viewFactory;
    }
}

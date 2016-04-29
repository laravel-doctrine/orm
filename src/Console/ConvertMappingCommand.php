<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use LaravelDoctrine\ORM\Console\Exporters\FluentExporter;

class ConvertMappingCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:convert:mapping
    {to-type : The mapping type to be converted.}
    {dest-path : The path to generate your entities classes.}
    {--em= : Generate getter and setter for a specific entity manager. }
    {--filter= : A string pattern used to match entities that should be processed.}
    {--force= : Force to overwrite existing mapping files.}
    {--from-database : Whether or not to convert mapping information from existing database.}
    {--extend= : Defines a base class to be extended by generated entity classes.}
    {--num-spaces=4 : Defines the number of indentation spaces}
    {--namespace= : Defines a namespace for the generated entity classes, if converted from database.}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Convert mapping information between supported formats.';

    /**
     * Fire the command
     * @param  ManagerRegistry                            $registry
     * @throws \Doctrine\ORM\Tools\Export\ExportException
     * @return int
     */
    public function fire(ManagerRegistry $registry)
    {
        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();

        foreach ($names as $name) {
            $em = $registry->getManager($name);

            if ($this->option('from-database') === true) {
                $databaseDriver = new DatabaseDriver(
                    $em->getConnection()->getSchemaManager()
                );

                $em->getConfiguration()->setMetadataDriverImpl(
                    $databaseDriver
                );

                if (($namespace = $this->option('namespace')) !== null) {
                    $databaseDriver->setNamespace($namespace);
                }
            }

            $cmf = new DisconnectedClassMetadataFactory();
            $cmf->setEntityManager($em);
            $metadata = $cmf->getAllMetadata();
            $metadata = MetadataFilter::filter($metadata, $this->option('filter'));

            // Process destination directory
            if (!is_dir($destPath = $this->argument('dest-path'))) {
                mkdir($destPath, 0775, true);
            }
            $destPath = realpath($destPath);

            if (!file_exists($destPath)) {
                throw new \InvalidArgumentException(
                    sprintf("Mapping destination directory '<info>%s</info>' does not exist.",
                        $this->argument('dest-path'))
                );
            }

            if (!is_writable($destPath)) {
                throw new \InvalidArgumentException(
                    sprintf("Mapping destination directory '<info>%s</info>' does not have write permissions.",
                        $destPath)
                );
            }

            $toType = strtolower($this->argument('to-type'));

            $exporter = $this->getExporter($toType, $destPath);
            $exporter->setOverwriteExistingFiles($this->option('force'));

            if ($toType == 'annotation') {
                $entityGenerator = new EntityGenerator();
                $exporter->setEntityGenerator($entityGenerator);

                $entityGenerator->setNumSpaces($this->option('num-spaces'));

                if (($extend = $this->option('extend')) !== null) {
                    $entityGenerator->setClassToExtend($extend);
                }
            }

            if (count($metadata)) {
                foreach ($metadata as $class) {
                    $this->info(sprintf('Processing entity "<info>%s</info>"', $class->name));
                }

                $exporter->setMetadata($metadata);
                $exporter->export();

                $this->info(PHP_EOL . sprintf(
                        'Exporting "<info>%s</info>" mapping information to "<info>%s</info>"', $toType, $destPath
                    ));
            } else {
                $this->info('No Metadata Classes to process.');
            }
        }
    }

    /**
     * @param string $toType
     * @param string $destPath
     *
     * @return \Doctrine\ORM\Tools\Export\Driver\AbstractExporter
     */
    protected function getExporter($toType, $destPath)
    {
        $cme = new ClassMetadataExporter();

        $cme->registerExportDriver('fluent', FluentExporter::class);

        return $cme->getExporter($toType, $destPath);
    }
}

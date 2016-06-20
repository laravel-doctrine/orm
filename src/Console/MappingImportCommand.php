<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;

class MappingImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:mapping:import
    {mapping-type=xml : The mapping type to export the imported mapping information to}
    {dest-path? : Location the mapping files should be imported to}
    {--em=default : Info for a specific entity manager }
    {--filter= : A string pattern used to match entities that should be mapped}
    {--force= : Force to overwrite existing mapping files}
    {--namespace= : Namespace to use}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Imports mapping information from an existing database';

    /**
     * Fire the command
     * @param  ManagerRegistry                            $registry
     * @throws \Doctrine\ORM\Tools\Export\ExportException
     * @return int
     */
    public function fire(ManagerRegistry $registry)
    {
        $emName = $this->option('em');
        $em     = $registry->getManager($emName);

        $destPath = base_path($this->argument('dest-path') ? $this->argument('dest-path') : 'app/Mappings');

        $simplified = false;
        $type       = $this->argument('mapping-type');

        if (starts_with($type, 'simplified')) {
            $simplified = true;

            if (str_contains($type, 'xml')) {
                $type       = 'xml';
            } elseif (str_contains($type, 'yaml')) {
                $type       = 'yaml';
            }
        }

        $cme      = new ClassMetadataExporter();
        $exporter = $cme->getExporter($type);

        $exporter->setOverwriteExistingFiles($this->option('force'));

        if ('annotation' === $type) {
            $entityGenerator = $this->getEntityGenerator();
            $exporter->setEntityGenerator($entityGenerator);
        }

        $databaseDriver = new DatabaseDriver($em->getConnection()->getSchemaManager());

        // set namespace that will be used to generate metadata files
        $namespace = $this->option('namespace');
        if ($namespace) {
            $databaseDriver->setNamespace($namespace);
        }

        $em->getConfiguration()->setMetadataDriverImpl($databaseDriver);

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);

        $metadata = $cmf->getAllMetadata();
        $metadata = MetadataFilter::filter($metadata, $this->option('filter'));

        if ($metadata) {
            $this->info(sprintf('Importing mapping information from "<info>%s</info>" entity manager', $emName));

            foreach ($metadata as $class) {
                $className = $class->name;
                if ('annotation' === $type) {
                    $path = $destPath . '/' . str_replace('\\', '.', $className) . '.php';
                } elseif ($simplified) {
                    $element = explode('\\', $className);
                    $path    = $destPath . '/' . end($element) . '.orm.' . $type;
                } else {
                    $path = $destPath . '/' . str_replace('\\', '.', $className) . '.dcm.' . $type;
                }
                $this->info(sprintf('  > writing <comment>%s</comment>', $path));
                $code = $exporter->exportClassMetadata($class);
                if (!is_dir($dir = dirname($path))) {
                    mkdir($dir, 0775, true);
                }
                file_put_contents($path, $code);
                chmod($path, 0664);
            }

            return 0;
        } else {
            $this->error('Database does not have any mapping information.');
            $this->error('');

            return 1;
        }
    }

    /**
     * get a doctrine entity generator
     *
     * @return EntityGenerator
     */
    protected function getEntityGenerator()
    {
        $entityGenerator = new EntityGenerator();

        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');

        return $entityGenerator;
    }
}

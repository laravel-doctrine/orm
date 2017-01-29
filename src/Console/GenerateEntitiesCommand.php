<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;

class GenerateEntitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:generate:entities
    {dest-path? : Path you want entities to be generated in }
    {--filter=* : A string pattern used to match entities that should be processed.}
    {--em= : Generate getter and setter for a specific entity manager. },
    {--generate-annotations : Flag to define if generator should generate annotation metadata on entities.}
    {--generate-methods : Flag to define if generator should generate stub methods on entities.}
    {--regenerate-entities : Flag to define if generator should regenerate entity if it exists.}
    {--update-entities : Flag to define if generator should only update entity if it exists.}
    {--extend= : Defines a base class to be extended by generated entity classes.}
    {--num-spaces=4 : Defines the number of indentation spaces.}
    {--no-backup : Flag to define if generator should avoid backuping existing entity file if it exists}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generates entities based on mapping files';

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

            $this->comment('');
            $this->message('Generating getter and setter for <info>' . $name . '</info> entity manager...', 'blue');

            $cmf = new DisconnectedClassMetadataFactory();
            $cmf->setEntityManager($em);
            $metadatas = $cmf->getAllMetadata();
            $metadatas = MetadataFilter::filter($metadatas, $this->option('filter'));

            $destPath = base_path($this->argument('dest-path') ?:'app/Entities');

            if (!is_dir($destPath)) {
                mkdir($destPath, 0777, true);
            }

            $destPath = realpath($destPath);

            if (!file_exists($destPath)) {
                throw new \InvalidArgumentException(
                    sprintf("Proxies destination directory ' < info>%s </info > ' does not exist.",
                        $em->getConfiguration()->getProxyDir())
                );
            }

            if (count($metadatas)) {

                // Create EntityGenerator
                $entityGenerator = new EntityGenerator();

                $entityGenerator->setGenerateAnnotations($this->option('generate-annotations'));
                $entityGenerator->setGenerateStubMethods($this->option('generate-methods'));
                $entityGenerator->setRegenerateEntityIfExists($this->option('regenerate-entities'));
                $entityGenerator->setUpdateEntityIfExists($this->option('update-entities'));
                $entityGenerator->setNumSpaces($this->option('num-spaces'));
                $entityGenerator->setBackupExisting(!$this->option('no-backup'));

                if (($extend = $this->option('extend')) !== null) {
                    $entityGenerator->setClassToExtend($extend);
                }

                foreach ($metadatas as $metadata) {
                    $this->comment(
                        sprintf('Processing entity "<info>%s</info>"', $metadata->name)
                    );
                }

                // Generating Entities
                $entityGenerator->generate($metadatas, $destPath);

                // Outputting information message
                $this->comment(PHP_EOL . sprintf('Entity classes generated to "<info>%s</INFO>"', $destPath));
            }
        }
    }
}

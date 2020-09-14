<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

class GenerateRepositoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:generate:repositories
    {dest-path? : The path to generate your proxy classes. If none is provided, it will attempt to grab from configuration.}
    {-- filter=* : A string pattern used to match entities that should be processed.}
    {--em= : Generate proxies for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generate repository classes from your entity classes.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function handle(ManagerRegistry $registry)
    {
        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();

        foreach ($names as $name) {
            /** @var EntityManagerInterface $em */
            $em = $registry->getManager($name);

            $this->comment('');
            $this->message('Generating repositories for <info>' . $name . '</info> entity manager...', 'blue');

            $metadatas = $em->getMetadataFactory()->getAllMetadata();
            $metadatas = MetadataFilter::filter($metadatas, $this->option('filter'));

            $repositoryName = $em->getConfiguration()->getDefaultRepositoryClassName();

            $destPath = base_path($this->argument('dest-path') ?:'app/Repositories');

            if (!is_dir($destPath)) {
                mkdir($destPath, 0777, true);
            }

            $destPath = realpath($destPath);

            if (!file_exists($destPath)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Repositories destination directory '<info>%s</info>' does not exist.",
                        $em->getConfiguration()->getProxyDir()
                    )
                );
            }

            if (!is_writable($destPath)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Repositories destination directory '<info>%s</info>' does not have write permissions.",
                        $destPath
                    )
                );
            }

            if (empty($metadatas)) {
                $this->error('No Metadata Classes to process.');

                return;
            }

            $numRepositories = 0;
            $generator       = new EntityRepositoryGenerator();

            $generator->setDefaultRepositoryName($repositoryName);

            foreach ($metadatas as $metadata) {
                if ($metadata->customRepositoryClassName) {
                    $this->comment(
                        sprintf('Processing repository "<info>%s</info>"', $metadata->customRepositoryClassName)
                    );

                    $generator->writeEntityRepositoryClass($metadata->customRepositoryClassName, $destPath);

                    ++$numRepositories;
                }
            }

            if ($numRepositories === 0) {
                $this->error('No Repository classes were found to be processed.');

                return;
            }

            $this->comment(sprintf('Repository classes generated to "<info>%s</info>"', $destPath));
        }
    }
}

<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use InvalidArgumentException;

class GenerateProxiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:generate:proxies
    {dest-path? : The path to generate your proxy classes. If none is provided, it will attempt to grab from configuration.}
    {-- filter=* : A string pattern used to match entities that should be processed.}
    {--em= : Generate proxies for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Generates proxy classes for entity classes.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function fire(ManagerRegistry $registry)
    {
        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();

        foreach ($names as $name) {
            $em = $registry->getManager($name);

            $this->comment('');
            $this->message('Generating proxies for <info>' . $name . '</info> entity manager...', 'blue');

            $metadatas = $em->getMetadataFactory()->getAllMetadata();
            $metadatas = MetadataFilter::filter($metadatas, $this->option('filter'));

            // Process destination directory
            if (($destPath = $this->argument('dest-path')) === null) {
                $destPath = $em->getConfiguration()->getProxyDir();
            }

            if (!is_dir($destPath)) {
                mkdir($destPath, 0777, true);
            }

            $destPath = realpath($destPath);

            if (!file_exists($destPath)) {
                throw new InvalidArgumentException(
                    sprintf("Proxies destination directory '<info>%s</info>' does not exist.",
                        $em->getConfiguration()->getProxyDir())
                );
            }

            if (!is_writable($destPath)) {
                throw new InvalidArgumentException(
                    sprintf("Proxies destination directory '<info>%s</info>' does not have write permissions.",
                        $destPath)
                );
            }

            if (count($metadatas)) {
                foreach ($metadatas as $metadata) {
                    $this->comment(
                        sprintf('Processing entity "<info>%s</info>"', $metadata->name)
                    );
                }

                // Generating Proxies
                $em->getProxyFactory()->generateProxyClasses($metadatas, $destPath);

                // Outputting information message
                $this->comment(sprintf('Proxy classes generated to "<info>%s</INFO>"', $destPath));
            } else {
                $this->error('No Metadata Classes to process.');
            }
        }
    }
}

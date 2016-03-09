<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaValidator;

class SchemaValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:schema:validate
    {--skip-mapping : Skip the mapping validation check}
    {--skip-sync : Skip checking if the mapping is in sync with the database}
    {--em= : Validate schema for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Validate the mapping files.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function fire(ManagerRegistry $registry)
    {
        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();
        $exit  = 0;

        foreach ($names as $name) {
            $em        = $registry->getManager($name);
            $validator = new SchemaValidator($em);

            $this->comment('');
            $this->message('Validating for <info>' . $name . '</info> entity manager...');

            if ($this->option('skip-mapping')) {
                $this->comment('Mapping]  Skipped mapping check.');
            } elseif ($errors = $validator->validateMapping()) {
                foreach ($errors as $className => $errorMessages) {
                    $this->error("[Mapping]  FAIL - The entity-class '" . $className . "' mapping is invalid:");
                    $this->comment('');

                    foreach ($errorMessages as $errorMessage) {
                        $this->message('* ' . $errorMessage, 'red');
                    }
                }

                $exit += 1;
            } else {
                $this->info('[Mapping]  OK - The mapping files are correct.');
            }

            if ($this->option('skip-sync')) {
                $this->comment('Database] SKIPPED - The database was not checked for synchronicity.');
            } elseif (!$validator->schemaInSyncWithMetadata()) {
                $this->error('[Database] FAIL - The database schema is not in sync with the current mapping file.');

                $exit += 2;
            } else {
                $this->info('[Database] OK - The database schema is in sync with the mapping files.');
            }
        }

        return $exit;
    }
}

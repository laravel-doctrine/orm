<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:schema:create
    {--sql : Dumps the generated SQL statements to the screen (does not execute them)}
    {--em= : Create schema for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function fire(ManagerRegistry $registry)
    {
        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();

        if (!$this->option('sql')) {
            $this->error('ATTENTION: This operation should not be executed in a production environment.');
        }

        foreach ($names as $name) {
            $em   = $registry->getManager($name);
            $tool = new SchemaTool($em);

            $this->message('Creating database schema for <info>' . $name . '</info> entity manager...', 'blue');

            if ($this->option('sql')) {
                $sql = $tool->getCreateSchemaSql($em->getMetadataFactory()->getAllMetadata());
                $this->comment('     ' . implode(';     ' . PHP_EOL, $sql));
            } else {
                $tool->createSchema($em->getMetadataFactory()->getAllMetadata());
            }
        }

        $this->info('Database schema created successfully!');
    }
}

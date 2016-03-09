<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaDropCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:schema:drop
    {--sql : Instead of trying to apply generated SQLs into EntityManager Storage Connection, output them.}
    {--force : Don\'t ask for the deletion of the database, but force the operation to run.}
    {--full : Instead of using the Class Metadata to detect the database table schema, drop ALL assets that the database contains. }
    {--em= : Drop schema for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Drop the complete database schema of EntityManager Storage Connection or generate the corresponding SQL output.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function fire(ManagerRegistry $registry)
    {
        $this->error('ATTENTION: This operation should not be executed in a production environment.');

        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();
        $exit  = 0;

        foreach ($names as $name) {
            $em   = $registry->getManager($name);
            $tool = new SchemaTool($em);

            $this->info('');
            $this->message('Checking scheme for <info>' . $name . '</info> entity manager...');

            if ($this->option('sql')) {
                if ($this->option('full')) {
                    $sql = $tool->getDropDatabaseSQL();
                } else {
                    $sql = $tool->getDropSchemaSQL($em->getMetadataFactory()->getAllMetadata());
                }
                $this->comment('     ' . implode(';     ' . PHP_EOL, $sql));
            } else {
                if ($this->option('force')) {
                    $this->message('Dropping database schema...');

                    if ($this->option('full')) {
                        $tool->dropDatabase();
                    } else {
                        $tool->dropSchema($em->getMetadataFactory()->getAllMetadata());
                    }

                    $this->info('Database schema dropped successfully!');
                }

                if ($this->option('full')) {
                    $sql = $tool->getDropDatabaseSQL();
                } else {
                    $sql = $tool->getDropSchemaSQL($em->getMetadataFactory()->getAllMetadata());
                }

                if (count($sql)) {
                    $pluralization = (1 === count($sql)) ? 'query was' : 'queries were';
                    $this->message(sprintf('The Schema-Tool would execute <info>"%s"</info> %s to update the database.',
                        count($sql), $pluralization));
                    $this->message('Please run the operation by passing one - or both - of the following options:');

                    $this->comment(sprintf('    <info>php artisan %s --force</info> to execute the command',
                        $this->getName()));
                    $this->comment(sprintf('    <info>php artisan %s --sql</info> to dump the SQL statements to the screen',
                        $this->getName()));

                    $exit = 1;
                } else {
                    $this->error('Nothing to drop. The database is empty!');
                }
            }
        }

        return $exit;
    }
}

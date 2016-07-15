<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:schema:update
    {--clean : If defined, all assets of the database which are not relevant to the current metadata will be dropped.}
    {--sql : Dumps the generated SQL statements to the screen (does not execute them)}
    {--force : Causes the generated SQL statements to be physically executed against your database}
    {--em= : Update schema for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata.';

    /**
     * Execute the console command.
     *
     * @param ManagerRegistry $registry
     */
    public function fire(ManagerRegistry $registry)
    {
        if (!$this->option('sql') && (!$this->laravel->environment('local') && !$this->option('force'))) {
            $this->error('ATTENTION: This operation should not be executed in a production environment.');
            $this->error('Use the incremental update to detect changes during development and use');
            $this->error('the SQL DDL provided to manually update your database in production.');
        }

        $names = $this->option('em') ? [$this->option('em')] : $registry->getManagerNames();

        foreach ($names as $name) {
            $em   = $registry->getManager($name);
            $tool = new SchemaTool($em);

            $this->comment('');
            $this->message('Checking if database connected to <info>' . $name . '</info> entity manager needs updating...',
                'blue');

            // Check if there are updates available
            $sql = $tool->getUpdateSchemaSql(
                $em->getMetadataFactory()->getAllMetadata(),
                !$this->option('clean')
            );

            if (0 === count($sql)) {
                return $this->error('Nothing to update - your database is already in sync with the current entity metadata.');
            }

            if ($this->option('sql')) {
                $this->comment('     ' . implode(';     ' . PHP_EOL, $sql));
            } else {
                if ($this->laravel->environment('local') || $this->option('force')) {
                    $this->message('Updating database schema...', 'blue');
                    $tool->updateSchema(
                        $em->getMetadataFactory()->getAllMetadata(),
                        !$this->option('clean')
                    );

                    $pluralization = (1 === count($sql)) ? 'query was' : 'queries were';
                    $this->info(sprintf('Database schema updated successfully! "<info>%s</info>" %s executed',
                        count($sql),
                        $pluralization));
                } else {
                    $this->message(sprintf('The Schema-Tool would execute <info>"%s"</info> queries to update the database.',
                        count($sql)));
                }
            }
        }

        if (!$this->option('sql') && (!$this->laravel->environment('local') && !$this->option('force'))) {
            $this->info('');
            $this->message('Please run the operation by passing one - or both - of the following options:');
            $this->comment(sprintf('    <info>php artisan %s --force</info> to execute the command',
                $this->getName()));
            $this->comment(sprintf('    <info>php artisan %s --sql</info> to dump the SQL statements to the screen',
                $this->getName()));

            return 1;
        }

        return 0;
    }
}

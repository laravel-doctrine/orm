<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Contracts\Config\Repository;

class DumpDatabaseCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doctrine:dump:sqlite
        {--connection=sqlite}
        {--em=}
        {--dump=tests/_data/dump.sql : Choose the path for your dump file}
        {--no-seeding : Disable seeding in the dump process}
        {--seeder=DatabaseSeeder : Choose the seeder class}
        {--binary=sqlite3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a dump from a certain connection';

    /**
     * @param ManagerRegistry $registry
     * @param Repository      $config
     */
    public function handle(ManagerRegistry $registry, Repository $config)
    {
        $this->registry = $registry;
        $this->config   = $config;

        $em         = $this->option('em') != '' ? $this->option('em') : $registry->getDefaultManagerName();
        $connection = $this->option('connection');

        $dumped = $this->connect($connection, $em)
                       ->dropSchema($em)
                       ->createSchema($em)
                       ->seed()
                       ->dump($em);

        if ($dumped) {
            $this->info('Database dump created successfully.');
        } else {
            $this->error('Something went wrong when creating database dump!');
        }
    }

    /**
     * @param  string $connection
     * @param  string $em
     * @return $this
     */
    private function connect($connection, $em)
    {
        // Change connection of given manager to the new format
        $settings               = $this->config->get('doctrine.managers.' . $em);
        $settings['connection'] = $connection;

        // Reset
        $this->registry->resetManager($em);

        // Add new manager with new connection
        $this->registry->addManager($em, $settings);
        $this->registry->addConnection($em);

        return $this;
    }

    /**
     * @param $em
     * @return $this
     */
    private function dropSchema($em)
    {
        $this->callSilent('doctrine:schema:drop', [
            '--force' => true,
            '--full'  => true,
            '--em'    => $em
        ]);

        return $this;
    }

    /**
     * @param $em
     * @return $this
     */
    private function createSchema($em)
    {
        $this->callSilent('doctrine:schema:create', [
            '--em' => $em
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    private function seed()
    {
        if (!$this->option('no-seeding')) {
            $this->call('db:seed', [
                '--class' => $this->option('seeder'),
                '--force' => true
            ]);
        }

        return $this;
    }

    /**
     * @param  string $em
     * @return bool
     */
    private function dump($em)
    {
        $conn = $this->registry->getManager($em)->getConnection();

        $db     = $conn->getDatabase();
        $binary = $this->option('binary');
        $dump   = base_path($this->option('dump'));

        $binary  = is_null($binary) ? 'sqlite3' : $binary;
        $command = "$binary $db .dump";
        $command .= " > $dump";
        exec($command, $output, $status);

        return $status == 0;
    }
}

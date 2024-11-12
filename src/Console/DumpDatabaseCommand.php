<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Config\Repository;

use function base_path;
use function exec;

class DumpDatabaseCommand extends Command
{
    protected ManagerRegistry $registry;

    protected Repository $config;

    protected function configure(): void
    {
        parent::configure();

        $this->setName('doctrine:dump:sqlite');
        $this->setDescription(<<<'EOF'
doctrine:dump:sqlite

{--connection=sqlite}
{--em=}
{--dump=tests/_data/dump.sql : Choose the path for your dump file}
{--no-seeding : Disable seeding in the dump process}
{--seeder=DatabaseSeeder : Choose the seeder class}
{--binary=sqlite3}
EOF);
    }

    public function handle(ManagerRegistry $registry, Repository $config): void
    {
        $this->registry = $registry;
        $this->config   = $config;

        $em         = $this->option('em') !== '' ? $this->option('em') : $registry->getDefaultManagerName();
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

    /** @return $this */
    private function connect(string $connection, string $em)
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

    private function dropSchema(mixed $em): self
    {
        $this->callSilent('doctrine:schema:drop', [
            '--force' => true,
            '--full'  => true,
            '--em'    => $em,
        ]);

        return $this;
    }

    private function createSchema(mixed $em): self
    {
        $this->callSilent('doctrine:schema:create', ['--em' => $em]);

        return $this;
    }

    /** @return $this */
    private function seed()
    {
        if (! $this->option('no-seeding')) {
            $this->call('db:seed', [
                '--class' => $this->option('seeder'),
                '--force' => true,
            ]);
        }

        return $this;
    }

    private function dump(string $em): bool
    {
        $conn = $this->registry->getManager($em)->getConnection();

        $db     = $conn->getDatabase();
        $binary = $this->option('binary');
        $dump   = base_path($this->option('dump'));

        $binary ??= 'sqlite3';
        $command = $binary . ' ' . $db . ' .dump';
        $command .= ' > ' . $dump;
        exec($command, $output, $status);

        return $status === 0;
    }
}

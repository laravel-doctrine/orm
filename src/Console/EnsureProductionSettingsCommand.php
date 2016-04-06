<?php

namespace LaravelDoctrine\ORM\Console;

use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;

class EnsureProductionSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'doctrine:ensure:production
    {--with-db : Flag to also inspect database connection existence.}
    {--em= : Ensure production settings for a specific entity manager }';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Verify that Doctrine is properly configured for a production environment.';

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

            try {
                $em->getConfiguration()->ensureProductionSettings();

                if ($this->option('with-db')) {
                    $em->getConnection()->connect();
                }
            } catch (Exception $e) {
                $this->error('Error for ' . $name . ' entity manager');
                $this->error($e->getMessage());

                return 1;
            }

            $this->comment('Environment for <info>' . $name . '</info> entity manager is correctly configured for production.');
        }
    }
}

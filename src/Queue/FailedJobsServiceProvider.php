<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Queue;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

class FailedJobsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app['events']->listen(JobProcessing::class, function (): void {
            $connection = $this->app['config']->get('queue.failed.connection', null);
            $tableName  = $this->app['config']->get('queue.failed.table', 'failed_jobs');

            $schema = $this->app['registry']
                ->getConnection($connection)
                ->createSchemaManager();

            if ($schema->tablesExist($tableName)) {
                return;
            }

            $schema->createTable(
                (new FailedJobTable($tableName))->build(),
            );
        });
    }
}

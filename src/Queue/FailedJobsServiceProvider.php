<?php

namespace LaravelDoctrine\ORM\Queue;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

class FailedJobsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['events']->listen(JobProcessing::class, function () {
            $connection = $this->app['config']->get('queue.failed.connection', null);
            $tableName  = $this->app['config']->get('queue.failed.table', 'failed_jobs');

            $schema = $this->app['registry']
                ->getConnection($connection)
                ->getSchemaManager();

            if (!$schema->tablesExist($tableName)) {
                $schema->createTable(
                    (new FailedJobTable($tableName))->build()
                );
            }
        });
    }
}

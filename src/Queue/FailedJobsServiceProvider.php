<?php

namespace LaravelDoctrine\ORM\Queue;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\DoctrineManager;

class FailedJobsServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->make(DoctrineManager::class)->addPaths([
            __DIR__,
        ]);
    }
}

<?php

namespace LaravelDoctrine\ORM\Queue;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\DoctrineManager;

class FailedJobsServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->app->make(DoctrineManager::class)->addPaths([
            __DIR__,
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
    }
}

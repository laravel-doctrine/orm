<?php

namespace LaravelDoctrine\ORM\Validation;

use Illuminate\Validation\ValidationServiceProvider;

class PresenceVerifierProvider extends ValidationServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the database presence verifier.
     *
     * @return void
     */
    protected function registerPresenceVerifier()
    {
        $this->app->singleton('validation.presence', function ($app) {
            return new DoctrinePresenceVerifier($app['registry']);
        });
    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return [
            'validator',
            'validation.presence'
        ];
    }
}

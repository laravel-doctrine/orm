<?php

namespace LaravelDoctrine\ORM;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\Validation\PresenceVerifierProvider;

class ValidationServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPresenceVerifierProvider();
    }


    /**
     * Register the deferred service provider for the validation presence verifier
     */
    protected function registerPresenceVerifierProvider()
    {
        $this->app->register(PresenceVerifierProvider::class);
    }

}

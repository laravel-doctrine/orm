<?php


namespace LaravelDoctrine\ORM;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\Validation\DoctrinePresenceVerifier;

class PresenceVerifierProvider extends ServiceProvider
{
    /**
     * In Laravel 5.2 Validation became a deferred service, so in order to override it we must do so from a
     * deferred provider.
     *
     * @var bool
     */
    protected $deferred = true;

    /**
     * Replace Laravel's presence validator with a Doctrine one. This lets us reference Entities through
     * classname.property rather than table.column.
     */
    public function register()
    {
        $this->app->singleton('validation.presence', DoctrinePresenceVerifier::class);
    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return [
            'validation.presence'
        ];
    }
}

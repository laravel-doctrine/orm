<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Validation;

use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationServiceProvider;

class PresenceVerifierProvider extends ValidationServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = true;

    /**
     * Register the validation factory.
     */
    protected function registerValidationFactory(): void
    {
        $this->app->singleton('validator', static function ($app) {
            $validator = new Factory($app['translator'], $app);

            // The validation presence verifier is responsible for determining the existence of
            // values in a given data collection which is typically a relational database or
            // other persistent data stores. It is used to check for "uniqueness" as well.
            if (isset($app['registry']) && isset($app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            return $validator;
        });
    }

    /**
     * Register the database presence verifier.
     */
    protected function registerPresenceVerifier(): void
    {
        $this->app->singleton('validation.presence', static function ($app) {
            return new DoctrinePresenceVerifier($app['registry']);
        });
    }

    /** @return string[] */
    public function provides(): array
    {
        return [
            'validator',
            'validation.presence',
        ];
    }
}

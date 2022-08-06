<?php

namespace LaravelDoctrine\ORM\Testing\Factories;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param  callable|array|int|null  $count
     * @param  callable|array  $state
     * @return \LaravelDoctrine\ORM\Testing\Factories\Factory<static>
     */
    public static function factory($count = null, $state = [])
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \LaravelDoctrine\ORM\Testing\Factories\Factory<static>|null
     */
    protected static function newFactory()
    {
        return null;
    }
}

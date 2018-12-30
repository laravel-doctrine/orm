<?php

namespace LaravelDoctrine\Tests\Mocks;

class BindableEntityWithInterface implements \LaravelDoctrine\ORM\Contracts\UrlRoutable
{
    public $id;

    public $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return strtolower($this->name);
    }

    public static function getRouteKeyName(): string
    {
        return 'name';
    }
}
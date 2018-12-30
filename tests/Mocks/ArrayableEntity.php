<?php

namespace LaravelDoctrine\Tests\Mocks;

use LaravelDoctrine\ORM\Serializers\Arrayable;

class ArrayableEntity
{
    use Arrayable;

    protected $id = 'IDVALUE';

    protected $name = 'NAMEVALUE';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}
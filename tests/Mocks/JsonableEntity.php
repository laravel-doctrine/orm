<?php

namespace LaravelDoctrine\Tests\Mocks;

use LaravelDoctrine\ORM\Serializers\Jsonable;

class JsonableEntity
{
    use Jsonable;

    protected $id = 'IDVALUE';

    protected $name = 'NAMEVALUE';

    protected $numeric = '1';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNumeric()
    {
        return $this->numeric;
    }
}
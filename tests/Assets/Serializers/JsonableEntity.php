<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Serializers;

use LaravelDoctrine\ORM\Serializers\Jsonable;

class JsonableEntity
{
    use Jsonable;

    protected string $id = 'IDVALUE';

    protected string $name = 'NAMEVALUE';

    protected string $numeric = '1';

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumeric(): string
    {
        return $this->numeric;
    }
}

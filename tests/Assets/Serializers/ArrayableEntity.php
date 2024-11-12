<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Serializers;

use LaravelDoctrine\ORM\Serializers\Arrayable;

class ArrayableEntity
{
    use Arrayable;

    protected string $id = 'IDVALUE';

    protected string $name = 'NAMEVALUE';

    /** @var array|string[] */
    protected array $list = ['item1', 'item2'];

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array|string[] */
    public function getList(): array
    {
        return $this->list;
    }
}

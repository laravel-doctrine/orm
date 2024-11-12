<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Middleware;

use function strtolower;

class BindableEntity
{
    public int $id;

    public string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return strtolower($this->name);
    }
}

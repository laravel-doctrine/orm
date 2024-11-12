<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Testing;

class AncestorHydrateableClass
{
    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }
}

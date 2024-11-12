<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Testing;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;

#[Entity]
class EntityStub
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    public mixed $id;

    #[Column(type: 'string')]
    public mixed $name;

    #[ManyToMany(targetEntity: 'EntityStub')]
    #[JoinTable(name: 'stub_stubs')]
    #[JoinColumn(name: 'owner_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'owned_id', referencedColumnName: 'id')]
    public mixed $others;
}

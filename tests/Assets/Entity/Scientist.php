<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Theory;

#[ORM\Entity(repositoryClass: 'LaravelDoctrineTest\ORM\Assets\Repository\ScientistRepository')]
class Scientist
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $firstName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $lastName;

    /** @var Collection|Theory[] */
    #[ORM\OneToMany(targetEntity: Theory::class, mappedBy: 'scientist')]
    private Collection $theories;
}

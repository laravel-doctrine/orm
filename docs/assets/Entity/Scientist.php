<?php

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Doctrine\ORM\Repository\ScientistRepository")]
class Scientist
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private $id;

    #[ORM\Column(type: "string", nullable: true)]
    private $firstName;

    #[ORM\Column(type: "string", nullable: false)]
    private $lastName;

    #[ORM\OneToMany(targetEntity: \Theory::class, mappedBy: "scientist")]
    private $theories;
}
<?php

namespace Brouwers\LaravelDoctrine\Migrations;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="migrations")
 */
class Migration
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    public $migration;

    /**
     * @ORM\Column(type="integer")
     */
    public $batch;

    /**
     * @param $migration
     * @param $batch
     */
    public function __construct($migration, $batch)
    {
        $this->migration = $migration;
        $this->batch     = $batch;
    }
}

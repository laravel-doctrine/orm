<?php

namespace LaravelDoctrine\ORM\Queue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="failed_jobs")
 */
class FailedJob
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $connection;

    /**
     * @ORM\Column(type="string")
     */
    protected $queue;

    /**
     * @ORM\Column(type="text")
     */
    protected $payload;

    /**
     * @ORM\Column(type="datetime", name="failed_at")
     */
    protected $failedAt;
}

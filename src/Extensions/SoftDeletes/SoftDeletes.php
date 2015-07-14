<?php

namespace Brouwers\LaravelDoctrine\Extensions\SoftDeletes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeletes
{
    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @var DateTime
     */
    protected $deletedAt;

    /**
     * @return DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTime $deletedAt
     */
    public function setDeletedAt(DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return new DateTime() > $this->deletedAt;
    }
}

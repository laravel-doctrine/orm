<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;

abstract class Connection implements Driver
{
    public function __construct(protected Repository $config)
    {
    }
}

<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration;

interface Driver
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): mixed;
}

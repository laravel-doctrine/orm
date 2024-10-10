<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Serializers;

trait Arrayable
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return (new ArraySerializer())->serialize($this);
    }
}

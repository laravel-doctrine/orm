<?php

namespace LaravelDoctrine\ORM\Serializers;

trait Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return (new ArraySerializer)->serialize($this);
    }
}

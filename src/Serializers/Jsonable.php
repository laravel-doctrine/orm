<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Serializers;

trait Jsonable
{
    public function toJson(int $options = 0): string
    {
        return (new JsonSerializer())->serialize($this, $options);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize(): mixed
    {
        return (new ArraySerializer())->serialize($this);
    }
}

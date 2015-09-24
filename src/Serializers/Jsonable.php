<?php

namespace LaravelDoctrine\ORM\Serializers;

trait Jsonable
{
    /**
     * @param  int    $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return (new JsonSerializer)->serialize($this, $options);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return (new ArraySerializer)->serialize($this);
    }
}

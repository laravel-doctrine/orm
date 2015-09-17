<?php

use LaravelDoctrine\ORM\Serializers\Jsonable;
use LaravelDoctrine\ORM\Serializers\JsonSerializer;

class JsonSerializerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JsonSerializer
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = new JsonSerializer;
    }

    public function test_can_serialize_to_array()
    {
        $array = $this->serializer->serialize(new JsonableEntity);

        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE"}', $array);
    }
}

class JsonableEntity
{
    use Jsonable;

    protected $id = 'IDVALUE';

    protected $name = 'NAMEVALUE';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}

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

    public function test_can_serialize_to_json()
    {
        $json = $this->serializer->serialize(new JsonableEntity);

        $this->assertJson($json);
        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE","numeric":"1"}', $json);
    }

    public function test_can_serialize_to_json_with_numeric_check()
    {
        $json = $this->serializer->serialize(new JsonableEntity(), JSON_NUMERIC_CHECK);

        $this->assertJson($json);
        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE","numeric":1}', $json);
    }
}

class JsonableEntity
{
    use Jsonable;

    protected $id = 'IDVALUE';

    protected $name = 'NAMEVALUE';

    protected $numeric = "1";

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNumeric()
    {
        return $this->numeric;
    }
}

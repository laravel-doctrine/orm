<?php

namespace LaravelDoctrine\Tests\Serializers;

use LaravelDoctrine\ORM\Serializers\JsonSerializer;

class JsonSerializerTest extends \PHPUnit\Framework\TestCase
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
        $json = $this->serializer->serialize(new \LaravelDoctrine\Tests\Mocks\JsonableEntity);

        $this->assertJson($json);
        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE","numeric":"1"}', $json);
    }

    public function test_can_serialize_to_json_with_numeric_check()
    {
        $json = $this->serializer->serialize(new \LaravelDoctrine\Tests\Mocks\JsonableEntity(), JSON_NUMERIC_CHECK);

        $this->assertJson($json);
        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE","numeric":1}', $json);
    }
}

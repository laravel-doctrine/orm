<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Serializers;

use LaravelDoctrine\ORM\Serializers\JsonSerializer;
use LaravelDoctrineTest\ORM\Assets\Serializers\JsonableEntity;
use LaravelDoctrineTest\ORM\TestCase;

use const JSON_NUMERIC_CHECK;

class JsonSerializerTest extends TestCase
{
    protected JsonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new JsonSerializer();

        parent::setUp();
    }

    public function testCanSerializeToJson(): void
    {
        $jsonableEntity = new JsonableEntity();

        $json = $this->serializer->serialize($jsonableEntity);
        $jsonableEntity->jsonSerialize();

        $this->assertEquals($jsonableEntity->toJson(), $json);

        $this->assertJson($json);
        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE","numeric":"1"}', $json);
    }

    public function testCanSerializeToJsonWithNumericCheck(): void
    {
        $json = $this->serializer->serialize(new JsonableEntity(), JSON_NUMERIC_CHECK);

        $this->assertJson($json);
        $this->assertEquals('{"id":"IDVALUE","name":"NAMEVALUE","numeric":1}', $json);
    }
}

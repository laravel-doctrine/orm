<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Serializers;

use LaravelDoctrine\ORM\Serializers\ArraySerializer;
use LaravelDoctrineTest\ORM\Assets\Serializers\ArrayableEntity;
use LaravelDoctrineTest\ORM\TestCase;

class ArraySerializerTest extends TestCase
{
    protected ArraySerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ArraySerializer();

        parent::setUp();
    }

    public function testCanSerializeToArray(): void
    {
        $arrayableEntity = new ArrayableEntity();

        $array = $this->serializer->serialize($arrayableEntity);

        $this->assertEquals($array, $arrayableEntity->toArray());

        $this->assertEquals([
            'id'   => 'IDVALUE',
            'name' => 'NAMEVALUE',
            'list' => ['item1', 'item2'],
        ], $array);
    }
}

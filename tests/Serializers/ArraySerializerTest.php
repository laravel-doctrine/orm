<?php

namespace LaravelDoctrine\Tests\Serializers;

use LaravelDoctrine\ORM\Serializers\ArraySerializer;

class ArraySerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ArraySerializer
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = new ArraySerializer;
    }

    public function test_can_serialize_to_array()
    {
        $array = $this->serializer->serialize(new \LaravelDoctrine\Tests\Mocks\ArrayableEntity);

        $this->assertEquals([
            'id'   => 'IDVALUE',
            'name' => 'NAMEVALUE'
        ], $array);
    }
}

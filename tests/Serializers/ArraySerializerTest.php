<?php

use LaravelDoctrine\ORM\Serializers\Arrayable;
use LaravelDoctrine\ORM\Serializers\ArraySerializer;

class ArraySerializerTest extends PHPUnit_Framework_TestCase
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
        $array = $this->serializer->serialize(new ArrayableEntity);

        $this->assertEquals([
            'id'   => 'IDVALUE',
            'name' => 'NAMEVALUE'
        ], $array);
    }
}

class ArrayableEntity
{
    use Arrayable;

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

<?php

use LaravelDoctrine\ORM\Serializers\Arrayable;
use LaravelDoctrine\ORM\Serializers\ArraySerializer;
use PHPUnit\Framework\TestCase;

class ArraySerializerTest extends TestCase
{
    /**
     * @var ArraySerializer
     */
    protected $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ArraySerializer;
    }

    public function test_can_serialize_to_array()
    {
        $array = $this->serializer->serialize(new ArrayableEntity);

        $this->assertEquals([
            'id'   => 'IDVALUE',
            'name' => 'NAMEVALUE',
            'list' => ['item1', 'item2']
        ], $array);
    }
}

class ArrayableEntity
{
    use Arrayable;

    protected $id = 'IDVALUE';

    protected $name = 'NAMEVALUE';

    protected $list = ['item1', 'item2'];

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getList()
    {
        return $this->list;
    }
}

<?php

namespace LaravelDoctrine\ORM\Serializers;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class ArraySerializer
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([$this->getNormalizer()]);
    }

    /**
     * @param $entity
     *
     * @return array
     */
    public function serialize($entity)
    {
        return $this->serializer->normalize($entity, 'array');
    }

    /**
     * @return GetSetMethodNormalizer
     */
    protected function getNormalizer()
    {
        return new GetSetMethodNormalizer;
    }
}

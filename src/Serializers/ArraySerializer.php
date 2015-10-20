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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->serializer = new Serializer([$this->getNormalizer()], [
            'array' => $this->getEncoder(),
        ]);
    }

    /**
     * @param $entity
     *
     * @return string
     */
    public function serialize($entity)
    {
        return $this->serializer->serialize($entity, 'array');
    }

    /**
     * @return GetSetMethodNormalizer
     */
    protected function getNormalizer()
    {
        return new GetSetMethodNormalizer;
    }

    /**
     * @return ArrayEncoder
     */
    protected function getEncoder()
    {
        return new ArrayEncoder;
    }
}

<?php

namespace LaravelDoctrine\ORM\Serializers;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

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
        $format = 'array';
        $data = $this->serializer->normalize($entity, $format);

        return $this->getEncoder()->encode($data, $format);
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

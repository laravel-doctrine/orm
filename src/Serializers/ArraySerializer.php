<?php

namespace LaravelDoctrine\ORM\Serializers;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class ArraySerializer
{
    /**
     * @param $entity
     *
     * @return array
     */
    public function serialize($entity)
    {
        return $this->getNormalizer()->normalize($entity, 'array');
    }

    /**
     * @return GetSetMethodNormalizer
     */
    protected function getNormalizer()
    {
        return new GetSetMethodNormalizer;
    }
}

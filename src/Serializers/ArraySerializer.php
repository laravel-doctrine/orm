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
        $format = 'array';
        $data   = $this->getNormalizer()->normalize($entity, $format);

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

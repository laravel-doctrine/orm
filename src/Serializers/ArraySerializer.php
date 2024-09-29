<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Serializers;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class ArraySerializer
{
    protected Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([$this->getNormalizer()]);
    }

    /** @return mixed[] */
    public function serialize(mixed $entity): array
    {
        return $this->serializer->normalize($entity, 'array');
    }

    protected function getNormalizer(): GetSetMethodNormalizer
    {
        return new GetSetMethodNormalizer();
    }
}

<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Serializers;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonSerializer
{
    protected Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([$this->getNormalizer()], [
            'json' => $this->getEncoder(),
        ]);
    }

    public function serialize(mixed $entity, int $jsonEncodeOptions = 0): string
    {
        return $this->serializer->serialize($entity, 'json', ['json_encode_options' => $jsonEncodeOptions]);
    }

    protected function getNormalizer(): GetSetMethodNormalizer
    {
        return new GetSetMethodNormalizer();
    }

    protected function getEncoder(): JsonEncoder
    {
        return new JsonEncoder();
    }
}

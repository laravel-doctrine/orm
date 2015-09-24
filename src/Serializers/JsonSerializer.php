<?php

namespace LaravelDoctrine\ORM\Serializers;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonSerializer
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
            'json' => $this->getEncoder(),
        ]);
    }

    /**
     * @param $entity
     *
     * @param  int    $jsonEncodeOptions
     * @return string
     */
    public function serialize($entity, $jsonEncodeOptions = 0)
    {
        return $this->serializer->serialize($entity, 'json', ['json_encode_options' => $jsonEncodeOptions]);
    }

    /**
     * @return GetSetMethodNormalizer
     */
    protected function getNormalizer()
    {
        return new GetSetMethodNormalizer;
    }

    /**
     * @return JsonEncoder
     */
    protected function getEncoder()
    {
        return new JsonEncoder;
    }
}

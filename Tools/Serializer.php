<?php


namespace AcMarche\UrbaWeb\Tools;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class Serializer
{
    public static function create(): SerializerInterface
    {
        $normalizers = [
            new GetSetMethodNormalizer(),
            new ObjectNormalizer(null, null, null, new ReflectionExtractor()),
            new ArrayDenormalizer(),
            new DateTimeNormalizer()
        ];
        $encoders    = [
            new XmlEncoder(),
            new JsonEncoder(),
        ];

        return new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
    }
}

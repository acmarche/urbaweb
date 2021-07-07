<?php


namespace AcMarche\UrbaWeb\Tools;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class Serializer
{
    public static function create(): SerializerInterface
    {
        $normalizers = [
            new GetSetMethodNormalizer(),
            new ObjectNormalizer(),
            new ArrayDenormalizer(),
        ];
        $encoders    = [
            new XmlEncoder(),
            new JsonEncoder(),
        ];

        return new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
    }
}

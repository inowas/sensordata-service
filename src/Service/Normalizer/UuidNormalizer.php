<?php

declare(strict_types=1);

namespace App\Service\Normalizer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UuidNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param mixed $data
     * @param string $class
     * @param null $format
     * @param array $context
     * @return array|object|UuidInterface
     */
    public function denormalize($data, string $class, string $format = null, array $context = array())
    {
        return Uuid::fromString($data);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_string($data) && is_a($type, UuidInterface::class, true) && Uuid::isValid($data);
    }

    public function normalize($object, string $format = null, array $context = array())
    {
        return $object->toString();
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof UuidInterface;
    }
}




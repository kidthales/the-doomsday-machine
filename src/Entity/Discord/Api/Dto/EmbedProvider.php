<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * https://discord.com/developers/docs/resources/message#embed-object-embed-provider-structure
 */
class EmbedProvider implements NormalizableInterface
{
    /**
     * @param string|null $name Name of provider.
     * @param string|null $url URL of provider.
     */
    public function __construct(public ?string $name = null, public ?string $url = null)
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return ArrayObject
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): ArrayObject
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        return new ArrayObject($data);
    }
}

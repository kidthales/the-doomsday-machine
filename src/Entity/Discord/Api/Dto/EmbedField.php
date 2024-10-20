<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-field-structure
 */
class EmbedField implements NormalizableInterface
{
    /**
     * @param string $name Name of the field.
     * @param string $value Value of the field.
     * @param bool|null $inline Whether or not this field should display inline.
     */
    public function __construct(public string $name, public string $value, public ?bool $inline = null)
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['name' => $this->name, 'value' => $this->value];

        if ($this->inline !== null) {
            $data['inline'] = $this->inline;
        }

        return $data;
    }
}

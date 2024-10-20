<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#select-menu-object-select-option-structure
 */
class SelectOption implements NormalizableInterface
{
    /**
     * @param string $label User-facing name of the option; max 100 characters.
     * @param string $value Dev-defined value of the option; max 100 characters.
     * @param string|null $description Additional description of the option; max 100 characters.
     * @param Emoji|null $emoji id, name, and animated.
     * @param bool|null $default Will show this option as selected by default.
     */
    public function __construct(
        public string  $label,
        public string  $value,
        public ?string $description = null,
        public ?Emoji  $emoji = null,
        public ?bool   $default = null
    )
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['label' => $this->label, 'value' => $this->value];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->emoji !== null) {
            $data['emoji'] = $normalizer->normalize($this->emoji, $format, $context);
        }

        if ($this->default !== null) {
            $data['default'] = $this->default;
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ComponentType;
use App\Entity\Discord\Api\Enumeration\TextInputStyle;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#text-input-object-text-input-structure
 */
class TextInputComponent extends AbstractComponent implements NormalizableInterface
{
    /**
     * @param string $custom_id Developer-defined identifier for the input; max 100 characters.
     * @param TextInputStyle $style The Text Input Style.
     * @param string $label Label for this component; max 45 characters.
     * @param int|null $min_length Minimum input length for a text input; min 0, max 4000.
     * @param int|null $max_length Maximum input length for a text input; min 1, max 4000.
     * @param bool|null $required Whether this component is required to be filled (endpoint defaults to true).
     * @param string|null $value Pre-filled value for this component; max 4000 characters.
     * @param string|null $placeholder Custom placeholder text if the input is empty; max 100 characters.
     */
    public function __construct(
        public string         $custom_id,
        public TextInputStyle $style,
        public string         $label,
        public ?int           $min_length = null,
        public ?int           $max_length = null,
        public ?bool          $required = null,
        public ?string        $value = null,
        public ?string        $placeholder = null
    )
    {
        parent::__construct(type: ComponentType::TextInput);
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = [
            'type' => $this->type->value,
            'custom_id' => $this->custom_id,
            'style' => $this->style->value,
            'label' => $this->label
        ];

        if ($this->min_length !== null) {
            $data['min_length'] = $this->min_length;
        }

        if ($this->max_length !== null) {
            $data['max_length'] = $this->max_length;
        }

        if ($this->required !== null) {
            $data['required'] = $this->required;
        }

        if ($this->value !== null) {
            $data['value'] = $this->value;
        }

        if ($this->placeholder !== null) {
            $data['placeholder'] = $this->placeholder;
        }

        return $data;
    }
}

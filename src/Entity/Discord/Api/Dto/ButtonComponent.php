<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ButtonStyle;
use App\Entity\Discord\Api\Enumeration\ComponentType;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#button-object-button-structure
 */
class ButtonComponent extends AbstractComponent implements NormalizableInterface
{
    /**
     * @param ButtonStyle $style A button style.
     * @param string|null $label Text that appears on the button; max 80 characters.
     * @param Emoji|null $emoji name, id, and animated.
     * @param string|null $custom_id Developer-defined identifier for the button; max 100 characters.
     * @param string|null $sku_id Identifier for a purchasable SKU, only available when using premium-style buttons.
     * @param string|null $url URL for link-style buttons.
     * @param bool|null $disabled Whether the button is disabled (defaults to false).
     */
    public function __construct(
        public ButtonStyle $style,
        public ?string     $label = null,
        public ?Emoji      $emoji = null,
        public ?string     $custom_id = null,
        public ?string     $sku_id = null,
        public ?string     $url = null,
        public ?bool       $disabled = null
    )
    {
        parent::__construct(type: ComponentType::Button);
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
        $data = ['type' => $this->type->value, 'style' => $this->style->value];

        if ($this->label !== null) {
            $data['label'] = $this->label;
        }

        if ($this->emoji !== null) {
            $data['emoji'] = $normalizer->normalize($this->emoji, $format, $context);
        }

        if ($this->custom_id !== null) {
            $data['custom_id'] = $this->custom_id;
        }

        if ($this->sku_id !== null) {
            $data['sku_id'] = $this->sku_id;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->disabled !== null) {
            $data['disabled'] = $this->disabled;
        }

        return $data;
    }
}

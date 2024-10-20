<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#select-menu-object-select-option-structure
 */
class SelectOption
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
}

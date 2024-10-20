<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ChannelType;
use App\Entity\Discord\Api\Enumeration\ComponentType;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#select-menu-object-select-menu-structure
 */
class SelectMenuComponent extends AbstractComponent
{
    /**
     * @param ComponentType $type Type of select menu component (text: 3, user: 5, role: 6, mentionable: 7,
     * channels: 8).
     * @param string $custom_id ID for the select menu; max 100 characters.
     * @param SelectOption[]|null $options Specified choices in a select menu (only required and available
     * for string selects (type 3); max 25.
     * @param ChannelType[]|null $channel_types List of channel types to include in the channel select component
     * (type 8).
     * @param string|null $placeholder Placeholder text if nothing is selected; max 150 characters.
     * @param SelectDefaultValue[]|null $default_values List of default values for auto-populated select menu
     * components; number of default values must be in the range defined by min_values and max_values.
     * @param int|null $min_values Minimum number of items that must be chosen (defaults to 1); min 0, max 25.
     * @param int|null $max_values Maximum number of items that can be chosen (defaults to 1); max 25.
     * @param bool|null $disabled Whether select menu is disabled (endpoint defaults to false).
     */
    public function __construct(
        ComponentType  $type,
        public string  $custom_id,
        public ?array  $options = null,
        public ?array  $channel_types = null,
        public ?string $placeholder = null,
        public ?array  $default_values = null,
        public ?int    $min_values = null,
        public ?int    $max_values = null,
        public ?bool   $disabled = null
    )
    {
        parent::__construct(type: $type);
    }
}

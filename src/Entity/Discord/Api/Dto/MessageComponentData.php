<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ComponentType;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-message-component-data-structure
 */
class MessageComponentData
{
    /**
     * @param string $custom_id ID for the message component; max 100 characters.
     * @param ComponentType|null $component_type Select menu or button component type.
     * @param string[]|null $values Selected menu values.
     * @param ResolvedData|null $resolved Auto-populated values (user, role, mentionable, & channel select
     * menus).
     */
    public function __construct(
        public string         $custom_id,
        public ?ComponentType $component_type,
        public ?array         $values = null,
        public ?ResolvedData  $resolved = null
    )
    {
    }
}

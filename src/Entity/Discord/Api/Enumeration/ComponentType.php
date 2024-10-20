<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#component-object-component-types
 */
enum ComponentType: int
{
    /**
     * Container for other components.
     */
    case ActionRow = 1;

    /**
     * Button object.
     */
    case Button = 2;

    /**
     * Select menu for picking from defined text options.
     */
    case StringSelect = 3;

    /**
     * Text input object.
     */
    case TextInput = 4;

    /**
     * Select menu for users.
     */
    case UserSelect = 5;

    /**
     * Select menu for roles.
     */
    case RoleSelect = 6;

    /**
     * Select menu for mentionables (users and roles).
     */
    case MentionableSelect = 7;

    /**
     * Select menu for channels.
     */
    case ChannelSelect = 8;
}

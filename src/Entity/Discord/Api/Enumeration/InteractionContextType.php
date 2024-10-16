<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-interaction-context-types
 */
enum InteractionContextType: int
{
    /**
     * Interaction can be used within servers.
     */
    case GUILD = 0;

    /**
     * Interaction can be used within DMs with the app's bot user.
     */
    case BOT_DM = 1;

    /**
     * Interaction can be used within Group DMs and DMs other than the app's bot user.
     */
    case PRIVATE_CHANNEL = 2;
}

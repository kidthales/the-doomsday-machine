<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#application-command-object-entry-point-command-handler-types
 */
enum EntryPointCommandHandlerType: int
{
    /**
     * The app handles the interaction using an interaction token.
     */
    case APP_HANDLER = 1;

    /**
     * Discord handles the interaction by launching an Activity and sending a follow-up message without coordinating
     * with the app.
     */
    case DISCORD_LAUNCH_ACTIVITY = 2;
}

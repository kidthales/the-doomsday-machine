<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/guild#guild-object-mutable-guild-features
 */
enum MutableGuildFeatures: string
{
    /**
     * Enables Community Features in the guild.
     */
    case COMMUNITY = 'COMMUNITY';

    /**
     * Enables discovery in the guild, making it publicly listed.
     */
    case DISCOVERABLE = 'DISCOVERABLE';

    /**
     * Pauses all invites/access to the server.
     */
    case INVITES_DISABLED = 'INVITES_DISABLED';

    /**
     * Disables alerts for join raids.
     */
    case RAID_ALERTS_DISABLED = 'RAID_ALERTS_DISABLED';
}

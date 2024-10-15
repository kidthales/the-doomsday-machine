<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/application#application-object-application-integration-types
 */
enum ApplicationIntegrationType: int
{
    /**
     * App is installable to servers.
     */
    case GUILD_INSTALL = 0;

    /**
     * App is installable to users.
     */
    case USER_INSTALL = 1;
}

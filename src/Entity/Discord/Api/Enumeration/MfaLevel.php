<?php

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/guild#guild-object-mfa-level
 */
enum MfaLevel: int
{
    /**
     * Guild has no MFA/2FA requirement for moderation actions.
     */
    case NONE = 0;

    /**
     * Guild has a 2FA requirement for moderation actions.
     */
    case ELEVATED = 1;
}

<?php

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/user#user-object-premium-types
 */
enum PremiumTypes: int
{
    case None = 0;
    case NitroClassic = 1;
    case Nitro = 2;
    case NitroBasic = 3;
}

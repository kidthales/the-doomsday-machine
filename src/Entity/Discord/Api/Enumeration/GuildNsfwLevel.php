<?php

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/guild#guild-object-guild-nsfw-level
 */
enum GuildNsfwLevel: int
{
    case DEFAULT = 0;
    case EXPLICIT = 1;
    case SAFE = 2;
    case AGE_RESTRICTED = 3;
}

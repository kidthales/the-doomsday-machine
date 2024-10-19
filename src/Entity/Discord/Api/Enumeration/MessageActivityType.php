<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/message#message-object-message-activity-types
 */
enum MessageActivityType: int
{
    case JOIN = 1;
    case SPECTATE = 2;
    case LISTEN = 3;
    case JOIN_REQUEST = 5;
}

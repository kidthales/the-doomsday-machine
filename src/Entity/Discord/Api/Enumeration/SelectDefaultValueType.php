<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#select-menu-object-select-default-value-structure
 */
enum SelectDefaultValueType: string
{
    case user = 'user';
    case role = 'role';
    case channel = 'channel';
}

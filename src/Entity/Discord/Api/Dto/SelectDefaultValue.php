<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\SelectDefaultValueType;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#select-menu-object-select-default-value-structure
 */
class SelectDefaultValue
{
    /**
     * @param string $id ID of a user, role, or channel.
     * @param SelectDefaultValueType $type Type of value that id represents. Either "user", "role", or "channel".
     */
    public function __construct(public string $id, public SelectDefaultValueType $type)
    {
    }
}

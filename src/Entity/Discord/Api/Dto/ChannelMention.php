<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ChannelType;

/**
 * @see https://discord.com/developers/docs/resources/message#channel-mention-object-channel-mention-structure
 */
class ChannelMention
{
    /**
     * @param string $id ID of the channel.
     * @param string $guild_id ID of the guild containing the channel.
     * @param ChannelType $type The type of channel.
     * @param string $name The name of the channel.
     */
    public function __construct(
        public string      $id,
        public string      $guild_id,
        public ChannelType $type,
        public string      $name
    )
    {
    }
}

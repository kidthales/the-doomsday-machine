<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\MessageReferenceType;

/**
 * @see https://discord.com/developers/docs/resources/message#message-reference-structure
 */
class MessageReference
{
    /**
     * @param MessageReferenceType|null $type Type of reference.
     * @param string|null $message_id ID of the originating message.
     * @param string|null $channel_id ID of the originating message's channel.
     * @param string|null $guild_id ID of the originating message's guild.
     * @param bool|null $fail_if_not_exists When sending, whether to error if the referenced message doesn't exist
     * instead of sending as a normal (non-reply) message, default true.
     */
    public function __construct(
        public ?MessageReferenceType $type = null,
        public ?string               $message_id = null,
        public ?string               $channel_id = null,
        public ?string               $guild_id = null,
        public ?bool                 $fail_if_not_exists = null
    )
    {
    }
}

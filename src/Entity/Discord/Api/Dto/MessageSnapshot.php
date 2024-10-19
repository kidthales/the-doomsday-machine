<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#message-snapshot-structure
 */
class MessageSnapshot
{
    /**
     * @param Message $message Minimal subset of fields in the forwarded message.
     */
    public function __construct(public Message $message)
    {
    }
}

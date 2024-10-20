<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\MessageActivityType;

/**
 * @see https://discord.com/developers/docs/resources/message#message-object-message-activity-structure
 */
class MessageActivity
{
    /**
     * @param MessageActivityType $type Type of message activity.
     * @param string|null $party_id party_id from a Rich Presence event.
     */
    public function __construct(public MessageActivityType $type, public ?string $party_id = null)
    {
    }
}

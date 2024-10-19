<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/message#message-reference-types
 */
enum MessageReferenceType: int
{
    /**
     * A standard reference used by replies. Coupled message field: referenced_message.
     */
    case DEFAULT = 0;

    /**
     * Reference used to point to a message at a point in time. Coupled message field: message_snapshot.
     */
    case FORWARD = 1;
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#message-call-object-message-call-object-structure
 */
class MessageCall
{
    /**
     * @param string[] $participants Array of user object ids that participated in the call.
     * @param string|null $ended_timestamp Time when call ended.
     */
    public function __construct(public array $participants, public ?string $ended_timestamp = null)
    {
    }
}

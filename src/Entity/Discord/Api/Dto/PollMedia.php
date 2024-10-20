<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-media-object-poll-media-object-structure
 */
class PollMedia
{
    /**
     * @param string|null $text
     * @param Emoji|null $emoji
     */
    public function __construct(public ?string $text = null, public ?Emoji $emoji = null)
    {
    }
}

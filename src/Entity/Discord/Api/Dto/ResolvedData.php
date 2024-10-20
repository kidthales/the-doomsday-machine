<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-resolved-data-structure
 */
class ResolvedData
{
    /**
     * @param User[]|null $users IDs and User objects.
     * @param GuildMember[]|null $members IDs and partial Member objects.
     * @param Role[]|null $roles IDs and Role objects.
     * @param Channel[]|null $channels IDs and partial Channel objects.
     * @param Message[]|null $messages IDs and partial Message objects.
     * @param Attachment[]|null $attachments IDs and attachment objects.
     */
    public function __construct(
        public ?array $users = null,
        public ?array $members = null,
        public ?array $roles = null,
        public ?array $channels = null,
        public ?array $messages = null,
        public ?array $attachments = null
    )
    {
    }
}

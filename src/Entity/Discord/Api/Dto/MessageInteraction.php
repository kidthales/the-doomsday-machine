<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\InteractionType;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#message-interaction-object-message-interaction-structure
 */
class MessageInteraction
{
    /**
     * @param string $id ID of the interaction.
     * @param InteractionType $type Type of interaction.
     * @param string $name Name of the application command, including subcommands and subcommand groups.
     * @param User $user User who invoked the interaction.
     * @param GuildMember|null $member Member who invoked the interaction in the guild.
     */
    public function __construct(
        public string          $id,
        public InteractionType $type,
        public string          $name,
        public User            $user,
        public ?GuildMember    $member = null,
    )
    {
    }
}

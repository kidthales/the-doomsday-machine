<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-application-command-data-structure
 */
class ApplicationCommandData
{
    /**
     * @param string $id ID of the invoked command.
     * @param string $name Name of the invoked command.
     * @param ApplicationCommandType $type Type of the invoked command.
     * @param ResolvedData|null $resolved Converted users + roles + channels + attachments.
     * @param ApplicationCommandInteractionDataOption[]|null $options Params + values from the user.
     * @param string|null $guild_id ID of the guild the command is registered to.
     * @param string|null $target_id ID of the user or message targeted by a user or message command.
     */
    public function __construct(
        public string                 $id,
        public string                 $name,
        public ApplicationCommandType $type,
        public ?ResolvedData          $resolved = null,
        public ?array                 $options = null,
        public ?string                $guild_id = null,
        public ?string                $target_id = null
    )
    {
    }
}

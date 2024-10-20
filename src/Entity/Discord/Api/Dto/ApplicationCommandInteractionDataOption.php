<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ApplicationCommandOptionType;

/**
 * https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-application-command-interaction-data-option-structure
 */
class ApplicationCommandInteractionDataOption
{
    /**
     * @param string $name Name of the parameter.
     * @param ApplicationCommandOptionType $type Value of application command option type.
     * @param mixed $value Value of the option resulting from user input.
     * @param ApplicationCommandInteractionDataOption[]|null $options Present if this option is a group or
     * subcommand.
     * @param bool|null $focused True if this option is the currently focused option for autocomplete.
     */
    public function __construct(
        public string                       $name,
        public ApplicationCommandOptionType $type,
        public mixed                        $value = null,
        public ?array                       $options = null,
        public ?bool                        $focused = null
    )
    {
    }
}

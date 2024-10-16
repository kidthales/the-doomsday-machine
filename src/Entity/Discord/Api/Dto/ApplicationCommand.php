<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use App\Entity\Discord\Api\Enumeration\ApplicationIntegrationType;
use App\Entity\Discord\Api\Enumeration\EntryPointCommandHandlerType;
use App\Entity\Discord\Api\Enumeration\InteractionContextType;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-structure
 */
class ApplicationCommand
{
    /**
     * Deprecated (use contexts instead); Indicates whether the command is available in DMs with the app, only for
     * globally-scoped commands. By default, commands are visible.
     * @var bool|null
     * @deprecated
     */
    public ?bool $dm_permission;

    /**
     * Not recommended for use as field will soon be deprecated. Indicates whether the command is enabled by default
     * when the app is added to a guild, defaults to true.
     * @var bool|null
     * @deprecated
     */
    public ?bool $default_permission;

    /**
     * @param string $id Unique ID of command.
     * @param string $name Name of command, 1-32 characters.
     * @param string $description Description for CHAT_INPUT commands, 1-100 characters. Empty string for USER and
     * MESSAGE commands.
     * @param string|null $default_member_permissions Set of permissions represented as a bit set.
     * @param string $version Autoincrementing version identifier updated during substantial record changes.
     * @param ApplicationCommandType|null $type Type of command, endpoint defaults to 1.
     * @param string|null $application_id ID of the parent application.
     * @param string|null $guild_id Guild ID of the command, if not global.
     * @param string[]|null $name_localizations Localization dictionary for name field. Values follow the
     * same restrictions as name.
     * @param string[]|null $description_localizations Localization dictionary for description field.
     * Values follow the same restrictions as description.
     * @param ApplicationCommandOption[]|null $options Parameters for the command, max of 25.
     * @param bool|null $dm_permission Deprecated (use contexts instead); Indicates whether the command is available in
     * DMs with the app, only for globally-scoped commands. By default, commands are visible.
     * @param bool|null $default_permission Not recommended for use as field will soon be deprecated. Indicates whether
     * the command is enabled by default when the app is added to a guild, endpoint defaults to true.
     * @param bool|null $nsfw Indicates whether the command is age-restricted, endpoint defaults to false.
     * @param ApplicationIntegrationType[]|null $integration_types Installation contexts where the command is available,
     * only for globally-scoped commands. Endpoint defaults to your app's configured contexts.
     * @param InteractionContextType[]|null $contexts Interaction context(s) where the command can be used, only for
     * globally-scoped commands. By default, all interaction context types included for new commands.
     * @param EntryPointCommandHandlerType|null $handler Determines whether the interaction is handled by the app's
     * interactions handler or by Discord.
     */
    public function __construct(
        public string                        $id,
        public string                        $name,
        public string                        $description,
        public ?string                       $default_member_permissions,
        public string                        $version,
        public ?ApplicationCommandType       $type = null,
        public ?string                       $application_id = null,
        public ?string                       $guild_id = null,
        public ?array                        $name_localizations = null,
        public ?array                        $description_localizations = null,
        public ?array                        $options = null,
        ?bool                                $dm_permission = null,
        ?bool                                $default_permission = null,
        public ?bool                         $nsfw = null,
        public ?array                        $integration_types = null,
        public ?array                        $contexts = null,
        public ?EntryPointCommandHandlerType $handler = null
    )
    {
        $this->dm_permission = $dm_permission;
        $this->default_permission = $default_permission;
    }
}

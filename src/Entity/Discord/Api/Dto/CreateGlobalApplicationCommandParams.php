<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use App\Entity\Discord\Api\Enumeration\ApplicationIntegrationType;
use App\Entity\Discord\Api\Enumeration\InteractionContextType;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#create-global-application-command-json-params
 */
class CreateGlobalApplicationCommandParams implements NormalizableInterface
{
    /**
     * Control whether a null value for the name_localizations field can be included in normalization.
     * @var bool
     */
    #[Ignore]
    public bool $normalizeNullNameLocalizations = false;

    /**
     * Control whether a null value for the description_localizations field can be included in normalization.
     * @var bool
     */
    #[Ignore]
    public bool $normalizeNullDescriptionLocalizations = false;

    /**
     * Control whether a null value for the default_member_permissions field can be included in normalization.
     * @var bool
     */
    #[Ignore]
    public bool $normalizeNullDefaultMemberPermissions = false;

    /**
     * Control whether a null value for the dm_permission field can be included in normalization.
     * @var bool
     */
    #[Ignore]
    public bool $normalizeNullDmPermission = false;

    /**
     * Deprecated (use contexts instead); Indicates whether the command is available in DMs with the app, only for
     * globally-scoped commands. By default, commands are visible.
     * @var bool|null
     * @deprecated
     */
    public ?bool $dm_permission;

    /**
     * Replaced by default_member_permissions and will be deprecated in the future. Indicates whether the command is
     * enabled by default when the app is added to a guild. Endpoint defaults to true.
     * @var bool|null
     * @deprecated
     */
    public ?bool $default_permission;

    /**
     * @param string $name Name of command, 1-32 characters.
     * @param array|null $name_localizations Localization dictionary for the name field. Values follow the same
     * restrictions as name.
     * @param string|null $description 1-100 character description for CHAT_INPUT commands.
     * @param array|null $description_localizations Localization dictionary for the description field. Values follow the
     * same restrictions as description.
     * @param ApplicationCommandOption[]|null $options The parameters for the command, max of 25.
     * @param string|null $default_member_permissions Set of permissions represented as a bit set.
     * @param bool|null $dm_permission Deprecated (use contexts instead); Indicates whether the command is available in
     * DMs with the app, only for globally-scoped commands. By default, commands are visible.
     * @param bool|null $default_permission Replaced by default_member_permissions and will be deprecated in the future.
     * Indicates whether the command is enabled by default when the app is added to a guild. Endpoint defaults to true.
     * @param ApplicationIntegrationType[]|null $integration_types Installation context(s) where the command is available.
     * @param InteractionContextType[]|null $contexts Interaction context(s) where the command can be used.
     * @param ApplicationCommandType|null $type Type of command, endpoint defaults 1 if not set.
     * @param bool|null $nsfw Indicates whether the command is age-restricted.
     */
    public function __construct(
        public string                  $name,
        public ?array                  $name_localizations = null,
        public ?string                 $description = null,
        public ?array                  $description_localizations = null,
        public ?array                  $options = null,
        public ?string                 $default_member_permissions = null,
        ?bool                          $dm_permission = null,
        ?bool                          $default_permission = null,
        public ?array                  $integration_types = null,
        public ?array                  $contexts = null,
        public ?ApplicationCommandType $type = null,
        public ?bool                   $nsfw = null
    )
    {
        $this->dm_permission = $dm_permission;
        $this->default_permission = $default_permission;
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['name' => $this->name];

        if ($this->name_localizations !== null || $this->normalizeNullNameLocalizations) {
            $data['name_localizations'] = $this->name_localizations;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->description_localizations !== null || $this->normalizeNullDescriptionLocalizations) {
            $data['description_localizations'] = $this->description_localizations;
        }

        if ($this->options !== null) {
            $data['options'] = $normalizer->normalize($this->options, $format, $context);
        }

        if ($this->default_member_permissions !== null || $this->normalizeNullDefaultMemberPermissions) {
            $data['default_member_permissions'] = $this->default_member_permissions;
        }

        if ($this->dm_permission !== null || $this->normalizeNullDmPermission) {
            $data['dm_permission'] = $this->dm_permission;
        }

        if ($this->default_permission !== null) {
            $data['default_permission'] = $this->default_permission;
        }

        if ($this->integration_types !== null) {
            $data['integration_types'] = $normalizer->normalize($this->integration_types, $format, $context);
        }

        if ($this->contexts !== null) {
            $data['contexts'] = $normalizer->normalize($this->contexts, $format, $context);
        }

        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }

        if ($this->nsfw !== null) {
            $data['nsfw'] = $this->nsfw;
        }

        return $data;
    }
}

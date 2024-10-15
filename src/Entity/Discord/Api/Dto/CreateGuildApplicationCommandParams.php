<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#create-guild-application-command-json-params
 */
class CreateGuildApplicationCommandParams implements NormalizableInterface
{
    /**
     * @param CreateGlobalApplicationCommandParams $params
     * @return self
     */
    public static function fromCreateGlobalApplicationCommandParams(
        CreateGlobalApplicationCommandParams $params
    ): self
    {
        return new self(
            name: $params->name,
            name_localizations: $params->name_localizations,
            description: $params->description,
            description_localizations: $params->description_localizations,
            options: $params->options,
            default_member_permissions: $params->default_member_permissions,
            default_permission: $params->default_permission,
            type: $params->type,
            nsfw: $params->nsfw
        );
    }

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
     * @param ApplicationCommandOption[]|null $options Parameters for the command, max of 25.
     * @param string|null $default_member_permissions Set of permissions represented as a bit set.
     * @param bool|null $default_permission Replaced by default_member_permissions and will be deprecated in the future.
     * Indicates whether the command is enabled by default when the app is added to a guild. Endpoint defaults to true.
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
        ?bool                          $default_permission = null,
        public ?ApplicationCommandType $type = null,
        public ?bool                   $nsfw = null
    )
    {
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

        if ($this->default_permission !== null) {
            $data['default_permission'] = $this->default_permission;
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

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ApplicationCommandOptionType;
use App\Entity\Discord\Api\Enumeration\ChannelType;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-option-structure
 */
class ApplicationCommandOption implements NormalizableInterface
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
     * @param ApplicationCommandOptionType $type Type of option.
     * @param string $name 1-32 character name.
     * @param string $description 1-100 character description.
     * @param string[]|null $name_localizations Localization dictionary for the name field. Values follow the same
     * restrictions as name.
     * @param string[]|null $description_localizations Localization dictionary for the description field. Values follow
     * the same restrictions as description.
     * @param bool|null $required Whether the parameter is required or optional. Endpoint default is false.
     * @param ApplicationCommandOptionChoice[]|null $choices Choices for the user to pick from, max 25.
     * @param ApplicationCommandOption[]|null $options If the option is a subcommand or subcommand group type, these
     * nested options will be the parameters or subcommands respectively; up to 25.
     * @param ChannelType[]|null $channel_types The channels shown will be restricted to these types.
     * @param float|int|null $min_value The minimum value permitted.
     * @param float|int|null $max_value The maximum value permitted.
     * @param int|null $min_length The minimum allowed length (minimum of 0, maximum of 6000).
     * @param int|null $max_length The maximum allowed length (minimum of 1, maximum of 6000).
     * @param bool|null $autocomplete If autocomplete interactions are enabled for this option.
     */
    public function __construct(
        public ApplicationCommandOptionType $type,
        public string                       $name,
        public string                       $description,
        public ?array                       $name_localizations = null,
        public ?array                       $description_localizations = null,
        public ?bool                        $required = null,
        public ?array                       $choices = null,
        public ?array                       $options = null,
        public ?array                       $channel_types = null,
        public float|int|null               $min_value = null,
        public float|int|null               $max_value = null,
        public ?int                         $min_length = null,
        public ?int                         $max_length = null,
        public ?bool                        $autocomplete = null
    )
    {
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
        $data = [
            'type' => $this->type->value,
            'name' => $this->name
        ];

        if ($this->name_localizations !== null || $this->normalizeNullNameLocalizations) {
            $data['name_localizations'] = $this->name_localizations;
        }

        $data['description'] = $this->description;

        if ($this->description_localizations !== null || $this->normalizeNullDescriptionLocalizations) {
            $data['description_localizations'] = $this->description_localizations;
        }

        if ($this->required !== null) {
            $data['required'] = $this->required;
        }

        if ($this->choices !== null) {
            $data['choices'] = $normalizer->normalize($this->choices, $format, $context);
        }

        if ($this->options !== null) {
            $data['options'] = $normalizer->normalize($this->options, $format, $context);
        }

        if ($this->channel_types !== null) {
            $data['channel_types'] = $normalizer->normalize($this->channel_types, $format, $context);
        }

        if ($this->min_value !== null) {
            $data['min_value'] = $this->min_value;
        }

        if ($this->max_value !== null) {
            $data['max_value'] = $this->max_value;
        }

        if ($this->min_length !== null) {
            $data['min_length'] = $this->min_length;
        }

        if ($this->max_length !== null) {
            $data['max_length'] = $this->max_length;
        }

        if ($this->autocomplete !== null) {
            $data['autocomplete'] = $this->autocomplete;
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-option-choice-structure
 */
class ApplicationCommandOptionChoice implements NormalizableInterface
{
    /**
     * Control whether a null value for the name_localizations field can be included in normalization.
     * @var bool
     */
    #[Ignore]
    public bool $normalizeNullNameLocalizations = false;

    /**
     * @param string $name 1-100 character choice name.
     * @param float|int|string $value Value for the choice, up to 100 characters if string.
     * @param array<string, string>|null $name_localizations Localization dictionary for the name field. Values follow
     * the same restrictions as name.
     */
    public function __construct(
        public string           $name,
        public float|int|string $value,
        public ?array           $name_localizations = null,
    )
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['name' => $this->name];

        if ($this->name_localizations !== null || $this->normalizeNullNameLocalizations) {
            $data['name_localizations'] = $this->name_localizations;
        }

        $data['value'] = $this->value;

        return $data;
    }
}

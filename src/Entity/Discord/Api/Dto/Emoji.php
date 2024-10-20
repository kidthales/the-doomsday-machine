<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/emoji#emoji-object-emoji-structure
 */
class Emoji implements NormalizableInterface
{
    /**
     * @param string|null $id Emoji id.
     * @param string|null $name Emoji name.
     * @param string[]|null $roles Roles allowed to use this emoji.
     * @param User|null $user User that created this emoji.
     * @param bool|null $require_colons Whether this emoji must be wrapped in colons.
     * @param bool|null $managed Whether this emoji is managed.
     * @param bool|null $animated Whether this emoji is animated.
     * @param bool|null $available Whether this emoji can be used, may be false due to loss of Server Boosts.
     */
    public function __construct(
        public ?string $id,
        public ?string $name,
        public ?array  $roles = null,
        public ?User   $user = null,
        public ?bool   $require_colons = null,
        public ?bool   $managed = null,
        public ?bool   $animated = null,
        public ?bool   $available = null
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
        $data = ['id' => $this->id, 'name' => $this->name];

        if ($this->roles !== null) {
            $data['roles'] = $this->roles;
        }

        if ($this->user !== null) {
            $data['user'] = $normalizer->normalize($this->user, $format, $context);
        }

        if ($this->require_colons !== null) {
            $data['require_colons'] = $this->require_colons;
        }

        if ($this->managed !== null) {
            $data['managed'] = $this->managed;
        }

        if ($this->animated !== null) {
            $data['animated'] = $this->animated;
        }

        if ($this->available !== null) {
            $data['available'] = $this->available;
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\PremiumType;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/user#user-object-user-structure
 */
class User implements NormalizableInterface
{
    /**
     * @param string $id The user's id.
     * @param string $username The user's username, not unique across the platform.
     * @param string $discriminator The user's Discord-tag.
     * @param string|null $global_name The user's display name, if it is set. For bots, this is the application name.
     * @param string|null $avatar The user's avatar hash.
     * @param bool|null $bot Whether the user belongs to an OAuth2 application.
     * @param bool|null $system Whether the user is an Official Discord System user (part of the urgent message system).
     * @param bool|null $mfa_enabled Whether the user has two factor enabled on their account.
     * @param string|null $banner The user's banner hash.
     * @param int|null $accent_color The user's banner color encoded as an integer representation of hexadecimal color
     * code.
     * @param string|null $locale The user's chosen language option.
     * @param bool|null $verified Whether the email on this account has been verified.
     * @param string|null $email The user's email.
     * @param int|null $flags The flags on a user's account.
     * @param PremiumType|null $premium_type The type of Nitro subscription on a user's account.
     * @param int|null $public_flags The public flags on a user's account.
     * @param AvatarDecorationData|null $avatar_decoration_data Data for the user's avatar decoration.
     */
    public function __construct(
        public string                $id,
        public string                $username,
        public string                $discriminator,
        public ?string               $global_name,
        public ?string               $avatar,
        public ?bool                 $bot = null,
        public ?bool                 $system = null,
        public ?bool                 $mfa_enabled = null,
        public ?string               $banner = null,
        public ?int                  $accent_color = null,
        public ?string               $locale = null,
        public ?bool                 $verified = null,
        public ?string               $email = null,
        public ?int                  $flags = null,
        public ?PremiumType          $premium_type = null,
        public ?int                  $public_flags = null,
        public ?AvatarDecorationData $avatar_decoration_data = null
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
            'id' => $this->id,
            'username' => $this->username,
            'discriminator' => $this->discriminator,
            'global_name' => $this->global_name,
            'avatar' => $this->avatar
        ];

        if ($this->bot !== null) {
            $data['bot'] = $this->bot;
        }

        if ($this->system !== null) {
            $data['system'] = $this->system;
        }

        if ($this->mfa_enabled !== null) {
            $data['mfa_enabled'] = $this->mfa_enabled;
        }

        $data['banner'] = $this->banner;
        $data['accent_color'] = $this->accent_color;

        if ($this->locale !== null) {
            $data['locale'] = $this->locale;
        }

        if ($this->verified !== null) {
            $data['verified'] = $this->verified;
        }

        $data['email'] = $this->email;

        if ($this->flags !== null) {
            $data['flags'] = $this->flags;
        }

        if ($this->premium_type !== null) {
            $data['premium_type'] = $this->premium_type->value;
        }

        if ($this->public_flags !== null) {
            $data['public_flags'] = $this->public_flags;
        }

        $data['avatar_decoration_data'] = $normalizer->normalize($this->avatar_decoration_data, $format, $context);

        return $data;
    }
}

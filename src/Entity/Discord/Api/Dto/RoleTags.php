<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @see https://discord.com/developers/docs/topics/permissions#role-object-role-tags-structure
 */
class RoleTags implements DenormalizableInterface
{
    /**
     * @param string|null $bot_id The id of the bot this role belongs to.
     * @param string|null $integration_id The id of the integration this role belongs to.
     * @param bool|null $premium_subscriber Whether this is the guild's Booster role.
     * @param string|null $subscription_listing_id The id of this role's subscription sku and listing.
     * @param bool|null $available_for_purchase Whether this role is available for purchase.
     * @param bool|null $guild_connections Whether this role is a guild's linked role.
     */
    public function __construct(
        public ?string $bot_id = null,
        public ?string $integration_id = null,
        public ?bool   $premium_subscriber = null,
        public ?string $subscription_listing_id = null,
        public ?bool   $available_for_purchase = null,
        public ?bool   $guild_connections = null
    )
    {
    }

    /**
     * @param DenormalizerInterface $denormalizer
     * @param float|int|bool|array|string $data
     * @param string|null $format
     * @param array $context
     * @return void
     */
    public function denormalize(
        DenormalizerInterface       $denormalizer,
        float|int|bool|array|string $data,
        ?string                     $format = null,
        array                       $context = []
    ): void
    {
        if (isset($data['bot_id'])) {
            $this->bot_id = $data['bot_id'];
        }

        if (isset($data['integration_id'])) {
            $this->integration_id = $data['integration_id'];
        }

        $this->premium_subscriber = array_key_exists('premium_subscriber', $data);

        if (isset($data['subscription_listing_id'])) {
            $this->subscription_listing_id = $data['subscription_listing_id'];
        }

        $this->available_for_purchase = array_key_exists('available_for_purchase', $data);
        $this->guild_connections = array_key_exists('guild_connections', $data);
    }
}

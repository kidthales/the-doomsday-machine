<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/sticker#sticker-item-object-sticker-item-structure
 */
class RoleSubscriptionData
{
    /**
     * @param string $role_subscription_listing_id The id of the sku and listing that the user is subscribed to.
     * @param string $tier_name The name of the tier that the user is subscribed to.
     * @param int $total_months_subscribed The cumulative number of months that the user has been subscribed for.
     * @param bool $is_removal Whether this notification is for a renewal rather than a new purchase.
     */
    public function __construct(
        public string $role_subscription_listing_id,
        public string $tier_name,
        public int    $total_months_subscribed,
        public bool   $is_removal
    )
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/entitlement#entitlement-object-entitlement-types
 */
enum EntitlementType: int
{
    /**
     * Entitlement was purchased by user.
     */
    case PURCHASE = 1;

    /**
     * Entitlement for Discord Nitro subscription.
     */
    case PREMIUM_SUBSCRIPTION = 2;

    /**
     * Entitlement was gifted by developer.
     */
    case DEVELOPER_GIFT = 3;

    /**
     * Entitlement was purchased by a dev in application test mode.
     */
    case TEST_MODE_PURCHASE = 4;

    /**
     * Entitlement was granted when the SKU was free.
     */
    case FREE_PURCHASE = 5;

    /**
     * Entitlement was gifted by another user.
     */
    case USER_GIFT = 6;

    /**
     * Entitlement was claimed by user for free as a Nitro Subscriber.
     */
    case PREMIUM_PURCHASE = 7;

    /**
     * Entitlement was purchased as an app subscription.
     */
    case APPLICATION_SUBSCRIPTION = 8;
}

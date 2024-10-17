<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\EntitlementType;

/**
 * @see https://discord.com/developers/docs/resources/entitlement#entitlement-object-entitlement-structure
 */
class Entitlement
{
    /**
     * @param string $id ID of the entitlement.
     * @param string $sku_id ID of the SKU.
     * @param string $application_id ID of the parent application.
     * @param EntitlementType $type Type of entitlement.
     * @param bool $deleted Entitlement was deleted.
     * @param string|null $user_id ID of the user that is granted access to the entitlement's sku.
     * @param string|null $starts_at Start date at which the entitlement is valid. Not present when using test
     * entitlements.
     * @param string|null $ends_at Date at which the entitlement is no longer valid. Not present when using test
     * entitlements.
     * @param string|null $guild_id ID of the guild that is granted access to the entitlement's sku.
     * @param bool|null $consumed For consumable items, whether or not the entitlement has been consumed.
     */
    public function __construct(
        public string          $id,
        public string          $sku_id,
        public string          $application_id,
        public EntitlementType $type,
        public bool            $deleted,
        public ?string         $user_id = null,
        public ?string         $starts_at = null,
        public ?string         $ends_at = null,
        public ?string         $guild_id = null,
        public ?bool           $consumed = null
    )
    {
    }
}

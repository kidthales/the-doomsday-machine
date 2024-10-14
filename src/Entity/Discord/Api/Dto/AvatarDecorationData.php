<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/user#avatar-decoration-data-object-avatar-decoration-data-structure
 */
class AvatarDecorationData
{
    /**
     * @param string $asset The avatar decoration hash.
     * @param string $sku_id ID of the avatar decoration's SKU.
     */
    public function __construct(public string $asset, public string $sku_id)
    {
    }
}

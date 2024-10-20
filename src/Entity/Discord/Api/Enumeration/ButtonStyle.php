<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#button-object-button-styles
 */
enum ButtonStyle: int
{
    /**
     * Blurple. Required field: custom_id.
     */
    case Primary = 1;

    /**
     * Grey. Required field: custom_id.
     */
    case Secondary = 2;

    /**
     * Green. Required field: custom_id.
     */
    case Success = 3;

    /**
     * Red. Required field: custom_id.
     */
    case Danger = 4;

    /**
     * Grey, navigates to a URL. Required field: url.
     */
    case Link = 5;

    /**
     * Blurple. Required field: sku_id.
     */
    case Premium = 6;
}

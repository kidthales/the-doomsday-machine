<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\StickerFormatType;

/**
 * @see https://discord.com/developers/docs/resources/sticker#sticker-item-object-sticker-item-structure
 */
class StickerItem
{
    /**
     * @param string $id ID of the sticker.
     * @param string $name Name of the sticker.
     * @param StickerFormatType $format_type Type of sticker format.
     */
    public function __construct(public string $id, public string $name, public StickerFormatType $format_type)
    {
    }
}

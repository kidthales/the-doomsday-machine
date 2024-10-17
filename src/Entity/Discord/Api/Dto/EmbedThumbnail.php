<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-thumbnail-structure
 */
class EmbedThumbnail
{
    /**
     * @param string $url Source url of thumbnail (only supports http(s) and attachments).
     * @param string|null $proxy_url A proxied url of the thumbnail.
     * @param int|null $height Height of thumbnail.
     * @param int|null $width Width of thumbnail.
     */
    public function __construct(
        public string  $url,
        public ?string $proxy_url = null,
        public ?int    $height = null,
        public ?int    $width = null
    )
    {
    }
}

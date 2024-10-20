<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-footer-structure
 */
class EmbedFooter
{
    /**
     * @param string $text Footer text.
     * @param string|null $icon_url URL of footer icon (only supports http(s) and attachments).
     * @param string|null $proxy_icon_url A proxied url of footer icon.
     */
    public function __construct(
        public string  $text,
        public ?string $icon_url = null,
        public ?string $proxy_icon_url = null
    )
    {
    }
}

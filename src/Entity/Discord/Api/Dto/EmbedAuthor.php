<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-author-structure
 */
class EmbedAuthor
{
    /**
     * @param string $name Name of author.
     * @param string|null $url URL of author (only supports http(s)).
     * @param string|null $icon_url URL of author icon (only supports http(s) and attachments).
     * @param string|null $proxy_icon_url A proxied url of author icon.
     */
    public function __construct(
        public string  $name,
        public ?string $url = null,
        public ?string $icon_url = null,
        public ?string $proxy_icon_url = null
    )
    {
    }
}

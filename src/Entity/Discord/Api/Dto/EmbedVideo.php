<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * https://discord.com/developers/docs/resources/message#embed-object-embed-video-structure
 */
class EmbedVideo
{
    /**
     * @param string|null $url Source url of video.
     * @param string|null $proxy_url A proxied url of the video.
     * @param int|null $height Height of video.
     * @param int|null $width Width of video.
     */
    public function __construct(
        public ?string $url = null,
        public ?string $proxy_url = null,
        public ?int    $height = null,
        public ?int    $width = null
    )
    {
    }
}

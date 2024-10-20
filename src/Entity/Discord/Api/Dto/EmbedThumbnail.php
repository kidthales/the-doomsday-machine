<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-thumbnail-structure
 */
class EmbedThumbnail implements NormalizableInterface
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

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return string[]
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['url' => $this->url];

        if ($this->proxy_url !== null) {
            $data['proxy_url'] = $this->proxy_url;
        }

        if ($this->height !== null) {
            $data['height'] = $this->height;
        }

        if ($this->width !== null) {
            $data['width'] = $this->width;
        }

        return $data;
    }
}

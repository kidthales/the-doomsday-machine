<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * https://discord.com/developers/docs/resources/message#embed-object-embed-video-structure
 */
class EmbedVideo implements NormalizableInterface
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

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return ArrayObject
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): ArrayObject
    {
        $data = [];

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->proxy_url !== null) {
            $data['proxy_url'] = $this->proxy_url;
        }

        if ($this->height !== null) {
            $data['height'] = $this->height;
        }

        if ($this->width !== null) {
            $data['width'] = $this->width;
        }

        return new ArrayObject($data);
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-author-structure
 */
class EmbedAuthor implements NormalizableInterface
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

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return string[]
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['name' => $this->name];

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->icon_url !== null) {
            $data['icon_url'] = $this->icon_url;
        }

        if ($this->proxy_icon_url !== null) {
            $data['proxy_icon_url'] = $this->proxy_icon_url;
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-footer-structure
 */
class EmbedFooter implements NormalizableInterface
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

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return string[]
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = ['text' => $this->text];

        if ($this->icon_url !== null) {
            $data['icon_url'] = $this->icon_url;
        }

        if ($this->proxy_icon_url !== null) {
            $data['proxy_icon_url'] = $this->proxy_icon_url;
        }

        return $data;
    }
}

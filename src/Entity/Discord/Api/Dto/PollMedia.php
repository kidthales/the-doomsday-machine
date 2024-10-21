<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use ArrayObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-media-object-poll-media-object-structure
 */
class PollMedia implements NormalizableInterface
{
    /**
     * @param string|null $text
     * @param Emoji|null $emoji
     */
    public function __construct(public ?string $text = null, public ?Emoji $emoji = null)
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return ArrayObject
     * @throws ExceptionInterface
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): ArrayObject
    {
        $data = [];

        if ($this->text !== null) {
            $data['text'] = $this->text;
        }

        if ($this->emoji !== null) {
            $data['emoji'] = $normalizer->normalize($this->emoji, $format, $context);
        }

        return new ArrayObject($data);
    }
}

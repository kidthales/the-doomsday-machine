<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\EmbedType;
use ArrayObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-structure
 */
class Embed implements NormalizableInterface
{
    /**
     * @param string|null $title Title of embed.
     * @param EmbedType|null $type Type of embed (always "rich" for webhook embeds).
     * @param string|null $description Description of embed.
     * @param string|null $url URL of embed.
     * @param string|null $timestamp Timestamp of embed content.
     * @param int|null $color Color code of the embed.
     * @param EmbedFooter|null $footer Footer information.
     * @param EmbedImage|null $image Image information
     * @param EmbedThumbnail|null $thumbnail Thumbnail information
     * @param EmbedVideo|null $video Video information.
     * @param EmbedProvider|null $provider Provider information.
     * @param EmbedAuthor|null $author Author information.
     * @param EmbedField[]|null $fields Fields information, max of 25.
     */
    public function __construct(
        public ?string         $title = null,
        public ?EmbedType      $type = null,
        public ?string         $description = null,
        public ?string         $url = null,
        public ?string         $timestamp = null,
        public ?int            $color = null,
        public ?EmbedFooter    $footer = null,
        public ?EmbedImage     $image = null,
        public ?EmbedThumbnail $thumbnail = null,
        public ?EmbedVideo     $video = null,
        public ?EmbedProvider  $provider = null,
        public ?EmbedAuthor    $author = null,
        public ?array          $fields = null
    )
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

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->timestamp !== null) {
            $data['timestamp'] = $this->timestamp;
        }

        if ($this->color !== null) {
            $data['color'] = $this->color;
        }

        if ($this->footer !== null) {
            $data['footer'] = $normalizer->normalize($this->footer, $format, $context);
        }

        if ($this->image !== null) {
            $data['image'] = $normalizer->normalize($this->image, $format, $context);
        }

        if ($this->thumbnail !== null) {
            $data['thumbnail'] = $normalizer->normalize($this->thumbnail, $format, $context);
        }

        if ($this->video !== null) {
            $data['video'] = $normalizer->normalize($this->video, $format, $context);
        }

        if ($this->provider !== null) {
            $data['provider'] = $normalizer->normalize($this->provider, $format, $context);
        }

        if ($this->author !== null) {
            $data['author'] = $normalizer->normalize($this->author, $format, $context);
        }

        if ($this->fields !== null) {
            $data['fields'] = $normalizer->normalize($this->fields, $format, $context);
        }

        return new ArrayObject($data);
    }
}

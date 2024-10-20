<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\EmbedType;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-structure
 */
class Embed
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
}

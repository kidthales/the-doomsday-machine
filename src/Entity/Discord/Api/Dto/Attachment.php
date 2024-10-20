<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#attachment-object-attachment-structure
 */
class Attachment
{
    /**
     * @param string $id Attachment id.
     * @param string $filename Name of file attached.
     * @param int $size Size of file in bytes.
     * @param string $url Source url of file.
     * @param string $proxy_url A proxied url of file.
     * @param string|null $title The title of the file.
     * @param string|null $description Description for the file (max 1024 characters).
     * @param string|null $content_type The attachment's media type.
     * @param int|null $height Height of file (if image).
     * @param int|null $width Width of file (if image).
     * @param bool|null $ephemeral Whether this attachment is ephemeral.
     * @param float|null $duration_secs The duration of the audio file (currently for voice messages).
     * @param string|null $waveform Base64 encoded bytearray representing a sampled waveform (currently for voice
     * messages).
     * @param int|null $flags Attachment flags combined as a bitfield.
     */
    public function __construct(
        public string  $id,
        public string  $filename,
        public int     $size,
        public string  $url,
        public string  $proxy_url,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $content_type = null,
        public ?int    $height = null,
        public ?int    $width = null,
        public ?bool   $ephemeral = null,
        public ?float  $duration_secs = null,
        public ?string $waveform = null,
        public ?int    $flags = null
    )
    {
    }
}

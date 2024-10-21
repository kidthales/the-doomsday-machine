<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Attachment;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Attachment
 */
final class AttachmentTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Attachment $expected
     * @param Attachment $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->filename, $actual->filename);
        self::assertSame($expected->size, $actual->size);
        self::assertSame($expected->url, $actual->url);
        self::assertSame($expected->proxy_url, $actual->proxy_url);
        self::assertSame($expected->title, $actual->title);
        self::assertSame($expected->description, $actual->description);
        self::assertSame($expected->content_type, $actual->content_type);
        self::assertSame($expected->height, $actual->height);
        self::assertSame($expected->width, $actual->width);
        self::assertSame($expected->ephemeral, $actual->ephemeral);
        self::assertSame($expected->duration_secs, $actual->duration_secs);
        self::assertSame($expected->waveform, $actual->waveform);
        self::assertSame($expected->flags, $actual->flags);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","filename":"test-filename",%s"size":8,"url":"test-url","proxy_url":"test-proxy-url"%s}';

        return [
            [
                sprintf($subjectTemplate, '', ',"height":null,"width":null'),
                new Attachment(
                    id: 'test-id',
                    filename: 'test-filename',
                    size: 8,
                    url: 'test-url',
                    proxy_url: 'test-proxy-url'
                )
            ],
            [
                sprintf($subjectTemplate, '"title":"test-title","description":"test-description","content_type":"test-content-type",', ',"height":10,"width":10,"ephemeral":true,"duration_secs":1.7,"waveform":"test-waveform","flags":42'),
                new Attachment(
                    id: 'test-id',
                    filename: 'test-filename',
                    size: 8,
                    url: 'test-url',
                    proxy_url: 'test-proxy-url',
                    title: 'test-title',
                    description: 'test-description',
                    content_type: 'test-content-type',
                    height: 10,
                    width: 10,
                    ephemeral: true,
                    duration_secs: 1.7,
                    waveform: 'test-waveform',
                    flags: 42
                )
            ]
        ];
    }

    /**
     * @param string $subject
     * @param Attachment $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Attachment $expected): void
    {
        self::testDeserialization($subject, $expected, Attachment::class);
    }

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $data = [];

        foreach (self::provider_deserialization() as [$template, $expected]) {
            $data[] = [$expected, $template];
        }

        return $data;
    }

    /**
     * @param Attachment $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(Attachment $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}

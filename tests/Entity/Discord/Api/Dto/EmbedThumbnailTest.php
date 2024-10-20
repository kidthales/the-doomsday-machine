<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\EmbedThumbnail;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\EmbedThumbnail
 */
final class EmbedThumbnailTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param EmbedThumbnail $expected
     * @param EmbedThumbnail $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->url, $actual->url);
        self::assertSame($expected->proxy_url, $actual->proxy_url);
        self::assertSame($expected->height, $actual->height);
        self::assertSame($expected->width, $actual->width);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"url":"test-url"%s}';

        return [
            [sprintf($subjectTemplate, ''), new EmbedThumbnail(url: 'test-url')],
            [
                sprintf($subjectTemplate, ',"proxy_url":"test-proxy-url"'),
                new EmbedThumbnail(url: 'test-url', proxy_url: 'test-proxy-url')
            ],
            [
                sprintf($subjectTemplate, ',"height":10'),
                new EmbedThumbnail(url: 'test-url', height: 10)
            ],
            [
                sprintf($subjectTemplate, ',"width":10'),
                new EmbedThumbnail(url: 'test-url', width: 10)
            ],
            [
                sprintf($subjectTemplate, ',"proxy_url":"test-proxy-url","height":10'),
                new EmbedThumbnail(url: 'test-url', proxy_url: 'test-proxy-url', height: 10)
            ],
            [
                sprintf($subjectTemplate, ',"proxy_url":"test-proxy-url","width":10'),
                new EmbedThumbnail(url: 'test-url', proxy_url: 'test-proxy-url', width: 10)
            ],
            [
                sprintf($subjectTemplate, ',"height":10,"width":10'),
                new EmbedThumbnail(url: 'test-url', height: 10, width: 10)
            ],
            [
                sprintf($subjectTemplate, ',"proxy_url":"test-proxy-url","height":10,"width":10'),
                new EmbedThumbnail(url: 'test-url', proxy_url: 'test-proxy-url', height: 10, width: 10)
            ]
        ];
    }

    /**
     * @param string $subject
     * @param EmbedThumbnail $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, EmbedThumbnail $expected): void
    {
        self::testDeserialization($subject, $expected, EmbedThumbnail::class);
    }
}

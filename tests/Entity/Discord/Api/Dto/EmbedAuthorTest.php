<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\EmbedAuthor;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\EmbedAuthor
 */
final class EmbedAuthorTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param EmbedAuthor $expected
     * @param EmbedAuthor $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->url, $actual->url);
        self::assertSame($expected->icon_url, $actual->icon_url);
        self::assertSame($expected->proxy_icon_url, $actual->proxy_icon_url);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"name":"test-name"%s}';

        return [
            [sprintf($subjectTemplate, ''), new EmbedAuthor(name: 'test-name')],
            [
                sprintf($subjectTemplate, ',"url":"test-url"'),
                new EmbedAuthor(name: 'test-name', url: 'test-url')
            ],
            [
                sprintf($subjectTemplate, ',"icon_url":"test-icon-url"'),
                new EmbedAuthor(name: 'test-name', icon_url: 'test-icon-url')
            ],
            [
                sprintf($subjectTemplate, ',"proxy_icon_url":"test-proxy-icon-url"'),
                new EmbedAuthor(name: 'test-name', proxy_icon_url: 'test-proxy-icon-url')
            ],
            [
                sprintf($subjectTemplate, ',"url":"test-url","icon_url":"test-icon-url"'),
                new EmbedAuthor(name: 'test-name', url: 'test-url', icon_url: 'test-icon-url')
            ],
            [
                sprintf($subjectTemplate, ',"url":"test-url","proxy_icon_url":"test-proxy-icon-url"'),
                new EmbedAuthor(name: 'test-name', url: 'test-url', proxy_icon_url: 'test-proxy-icon-url')
            ],
            [
                sprintf($subjectTemplate, ',"icon_url":"test-icon-url","proxy_icon_url":"test-proxy-icon-url"'),
                new EmbedAuthor(name: 'test-name', icon_url: 'test-icon-url', proxy_icon_url: 'test-proxy-icon-url')
            ],
            [
                sprintf($subjectTemplate, ',"url":"test-url","icon_url":"test-icon-url","proxy_icon_url":"test-proxy-icon-url"'),
                new EmbedAuthor(name: 'test-name', url: 'test-url', icon_url: 'test-icon-url', proxy_icon_url: 'test-proxy-icon-url')
            ],
        ];
    }

    /**
     * @param string $subject
     * @param EmbedAuthor $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, EmbedAuthor $expected): void
    {
        self::testDeserialization($subject, $expected, EmbedAuthor::class);
    }

    /**
     * @return array[]
     */
    public static function provider_serialization(): array
    {
        return [
            [new EmbedAuthor(name: 'test-name'), '{"name":"test-name"}'],
            [
                new EmbedAuthor(
                    name: 'test-name',
                    url: 'test-url',
                    icon_url: 'test-icon-url',
                    proxy_icon_url: 'test-proxy-icon-url'
                ),
                '{"name":"test-name","url":"test-url","icon_url":"test-icon-url","proxy_icon_url":"test-proxy-icon-url"}'
            ]
        ];
    }

    /**
     * @param EmbedAuthor $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(EmbedAuthor $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}

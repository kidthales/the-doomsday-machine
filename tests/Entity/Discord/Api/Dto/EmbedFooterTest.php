<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\EmbedFooter;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\EmbedFooter
 */
final class EmbedFooterTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param EmbedFooter $expected
     * @param EmbedFooter $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->text, $actual->text);
        self::assertSame($expected->icon_url, $actual->icon_url);
        self::assertSame($expected->proxy_icon_url, $actual->proxy_icon_url);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"text":"test-text"%s}';

        return [
            [sprintf($subjectTemplate, ''), new EmbedFooter(text: 'test-text')],
            [
                sprintf($subjectTemplate, ',"icon_url":"test-icon-url"'),
                new EmbedFooter(text: 'test-text', icon_url: 'test-icon-url'),
            ],
            [
                sprintf($subjectTemplate, ',"proxy_icon_url":"test-proxy-icon-url"'),
                new EmbedFooter(text: 'test-text', proxy_icon_url: 'test-proxy-icon-url'),
            ],
            [
                sprintf($subjectTemplate, ',"icon_url":"test-icon-url","proxy_icon_url":"test-proxy-icon-url"'),
                new EmbedFooter(text: 'test-text', icon_url: "test-icon-url", proxy_icon_url: 'test-proxy-icon-url'),
            ]
        ];
    }

    /**
     * @param string $subject
     * @param EmbedFooter $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, EmbedFooter $expected): void
    {
        self::testDeserialization($subject, $expected, EmbedFooter::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\EmbedProvider;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\EmbedProvider
 */
final class EmbedProviderTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param EmbedProvider $expected
     * @param EmbedProvider $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->url, $actual->url);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s}';

        return [
            [sprintf($subjectTemplate, ''), new EmbedProvider()],
            [sprintf($subjectTemplate, '"name":"test-name"'), new EmbedProvider(name: 'test-name')],
            [sprintf($subjectTemplate, '"url":"test-url"'), new EmbedProvider(url: 'test-url')],
            [
                sprintf($subjectTemplate, '"name":"test-name","url":"test-url"'),
                new EmbedProvider(name: 'test-name', url: 'test-url')
            ]
        ];
    }

    /**
     * @param string $subject
     * @param EmbedProvider $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, EmbedProvider $expected): void
    {
        self::testDeserialization($subject, $expected, EmbedProvider::class);
    }
}

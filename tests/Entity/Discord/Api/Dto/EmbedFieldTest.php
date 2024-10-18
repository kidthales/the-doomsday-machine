<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\EmbedField;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\EmbedField
 */
final class EmbedFieldTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param EmbedField $expected
     * @param EmbedField $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->value, $actual->value);
        self::assertSame($expected->inline, $actual->inline);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"name":"test-name","value":"test-value"%s}';

        return [
            [sprintf($subjectTemplate, ''), new EmbedField(name: 'test-name', value: 'test-value')],
            [
                sprintf($subjectTemplate, ',"inline":true'),
                new EmbedField(name: 'test-name', value: 'test-value', inline: true),
            ]
        ];
    }

    /**
     * @param string $subject
     * @param EmbedField $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, EmbedField $expected): void
    {
        self::testDeserialization($subject, $expected, EmbedField::class);
    }
}

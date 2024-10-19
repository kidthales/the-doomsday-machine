<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\SelectOption;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\SelectOption
 */
final class SelectOptionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param SelectOption $expected
     * @param SelectOption $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->label, $actual->label);
        self::assertSame($expected->value, $actual->value);
        self::assertSame($expected->description, $actual->description);

        if (isset($expected->emoji)) {
            EmojiTest::assertDeepSame($expected->emoji, $actual->emoji);
        } else {
            self::assertNull($actual->emoji);
        }

        self::assertSame($expected->default, $actual->default);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"label":"test-label","value":"test-value"%s}';

        $data = [];

        foreach (EmojiTest::provider_deserialization() as [$emojiTemplate, $emojiExpected]) {
            $data[] = [
                sprintf($subjectTemplate, ',"description":"test-description","emoji":' . $emojiTemplate . ',"default":true'),
                new SelectOption(
                    label: 'test-label',
                    value: 'test-value',
                    description: 'test-description',
                    emoji: $emojiExpected,
                    default: true
                )
            ];
        }

        return [
            [sprintf($subjectTemplate, ''), new SelectOption(label: 'test-label', value: 'test-value')],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param SelectOption $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, SelectOption $expected): void
    {
        self::testDeserialization($subject, $expected, SelectOption::class);
    }
}

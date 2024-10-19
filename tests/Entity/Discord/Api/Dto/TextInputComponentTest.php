<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\TextInputComponent;
use App\Entity\Discord\Api\Enumeration\TextInputStyle;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\TextInputComponent
 * @covers \App\Entity\Discord\Api\Dto\AbstractComponent
 */
final class TextInputComponentTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param TextInputComponent $expected
     * @param TextInputComponent $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->custom_id, $actual->custom_id);
        self::assertSame($expected->style, $actual->style);
        self::assertSame($expected->label, $actual->label);
        self::assertSame($expected->min_length, $actual->min_length);
        self::assertSame($expected->max_length, $actual->max_length);
        self::assertSame($expected->required, $actual->required);
        self::assertSame($expected->value, $actual->value);
        self::assertSame($expected->placeholder, $actual->placeholder);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"type":4,"custom_id":"test-custom-id","style":%s,"label":"test-label"%s}';

        $data = [];

        foreach (TextInputStyle::cases() as $style) {
            $data[] = [
                sprintf($subjectTemplate, $style->value, ',"min_length":1,"max_length":100,"required":true,"value":"test-value","placeholder":"test-placeholder"'),
                new TextInputComponent(
                    custom_id: 'test-custom-id',
                    style: $style,
                    label: 'test-label',
                    min_length: 1,
                    max_length: 100,
                    required: true,
                    value: 'test-value',
                    placeholder: 'test-placeholder'
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, TextInputStyle::Short->value, ''),
                new TextInputComponent(custom_id: 'test-custom-id', style: TextInputStyle::Short, label: 'test-label')
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param TextInputComponent $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, TextInputComponent $expected): void
    {
        self::testDeserialization($subject, $expected, TextInputComponent::class);
    }
}

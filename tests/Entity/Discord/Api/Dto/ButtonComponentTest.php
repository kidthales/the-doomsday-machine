<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ButtonComponent;
use App\Entity\Discord\Api\Enumeration\ButtonStyle;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ButtonComponent
 * @covers \App\Entity\Discord\Api\Dto\AbstractComponent
 */
final class ButtonComponentTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ButtonComponent $expected
     * @param ButtonComponent $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->style, $actual->style);
        self::assertSame($expected->label, $actual->label);

        if (isset($expected->emoji)) {
            EmojiTest::assertDeepSame($expected->emoji, $actual->emoji);
        } else {
            self::assertNull($actual->emoji);
        }

        self::assertSame($expected->custom_id, $actual->custom_id);
        self::assertSame($expected->sku_id, $actual->sku_id);
        self::assertSame($expected->url, $actual->url);
        self::assertSame($expected->disabled, $actual->disabled);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"type":2,"style":%s%s}';

        $data = [];

        foreach (ButtonStyle::cases() as $style) {
            foreach (EmojiTest::provider_deserialization() as [$emojiTemplate, $emojiExpected]) {
                $data[] = [
                    sprintf($subjectTemplate, $style->value, ',"label":"test-label","emoji":' . $emojiTemplate . ',"custom_id":"test-custom-id","sku_id":"test-sku-id","url":"test-url","disabled":true'),
                    new ButtonComponent(
                        style: $style,
                        label: 'test-label',
                        emoji: $emojiExpected,
                        custom_id: 'test-custom-id',
                        sku_id: 'test-sku-id',
                        url: 'test-url',
                        disabled: true
                    )
                ];
            }
        }

        return [
            [
                sprintf($subjectTemplate, ButtonStyle::Primary->value, ''),
                new ButtonComponent(style: ButtonStyle::Primary)
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param ButtonComponent $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ButtonComponent $expected): void
    {
        self::testDeserialization($subject, $expected, ButtonComponent::class);
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
     * @param ButtonComponent $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(ButtonComponent $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}

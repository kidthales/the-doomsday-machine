<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\StickerItem;
use App\Entity\Discord\Api\Enumeration\StickerFormatType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\StickerItem
 */
final class StickerItemTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param StickerItem $expected
     * @param StickerItem $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->format_type, $actual->format_type);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","name":"test-name","format_type":%s}';

        $data = [];

        foreach (StickerFormatType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, $type->value),
                new StickerItem(id: 'test-id', name: 'test-name', format_type: $type)
            ];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param StickerItem $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, StickerItem $expected): void
    {
        self::testDeserialization($subject, $expected, StickerItem::class);
    }
}

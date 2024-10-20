<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\SelectDefaultValue;
use App\Entity\Discord\Api\Enumeration\SelectDefaultValueType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\SelectDefaultValue
 */
final class SelectDefaultValueTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param SelectDefaultValue $expected
     * @param SelectDefaultValue $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->type, $actual->type);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","type":"%s"}';

        $data = [];

        foreach (SelectDefaultValueType::cases() as $type) {
            $data[] = [sprintf($subjectTemplate, $type->value), new SelectDefaultValue(id: 'test-id', type: $type)];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param SelectDefaultValue $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, SelectDefaultValue $expected): void
    {
        self::testDeserialization($subject, $expected, SelectDefaultValue::class);
    }
}

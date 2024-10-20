<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Overwrite;
use App\Entity\Discord\Api\Enumeration\OverwriteType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Overwrite
 */
final class OverwriteTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Overwrite $expected
     * @param Overwrite $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->allow, $actual->allow);
        self::assertSame($expected->deny, $actual->deny);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","type":%s,"allow":"test-allow","deny":"test-deny"}';

        $data = [];

        foreach (OverwriteType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, $type->value),
                new Overwrite(
                    id: 'test-id',
                    type: $type,
                    allow: 'test-allow',
                    deny: 'test-deny'
                )
            ];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param Overwrite $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Overwrite $expected): void
    {
        self::testDeserialization($subject, $expected, Overwrite::class);
    }
}

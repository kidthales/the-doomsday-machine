<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Entitlement;
use App\Entity\Discord\Api\Enumeration\EntitlementType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Entitlement
 */
final class EntitlementTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Entitlement $expected
     * @param Entitlement $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->sku_id, $actual->sku_id);
        self::assertSame($expected->application_id, $actual->application_id);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->deleted, $actual->deleted);
        self::assertSame($expected->user_id, $actual->user_id);
        self::assertSame($expected->starts_at, $actual->starts_at);
        self::assertSame($expected->ends_at, $actual->ends_at);
        self::assertSame($expected->guild_id, $actual->guild_id);
        self::assertSame($expected->consumed, $actual->consumed);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","sku_id":"test-sku-id","application_id":"test-application-id","type":%s,"deleted":false%s}';

        $data = [];

        foreach (EntitlementType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, $type->value, ',"user_id":"test-user-id","starts_at":"test-starts-at","ends_at":"test-ends-at","guild_id":"test-guild-id","consumed":false'),
                new Entitlement(
                    id: 'test-id',
                    sku_id: 'test-sku-id',
                    application_id: 'test-application-id',
                    type: $type,
                    deleted: false,
                    user_id: 'test-user-id',
                    starts_at: 'test-starts-at',
                    ends_at: 'test-ends-at',
                    guild_id: 'test-guild-id',
                    consumed: false
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, EntitlementType::PURCHASE->value, ''),
                new Entitlement(
                    id: 'test-id',
                    sku_id: 'test-sku-id',
                    application_id: 'test-application-id',
                    type: EntitlementType::PURCHASE,
                    deleted: false
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param Entitlement $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Entitlement $expected): void
    {
        self::testDeserialization($subject, $expected, Entitlement::class);
    }
}

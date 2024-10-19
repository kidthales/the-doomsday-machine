<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\RoleSubscriptionData;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\RoleSubscriptionData
 */
final class RoleSubscriptionDataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param RoleSubscriptionData $expected
     * @param RoleSubscriptionData $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->role_subscription_listing_id, $actual->role_subscription_listing_id);
        self::assertSame($expected->tier_name, $actual->tier_name);
        self::assertSame($expected->total_months_subscribed, $actual->total_months_subscribed);
        self::assertSame($expected->is_removal, $actual->is_removal);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        return [
            [
                '{"role_subscription_listing_id":"test-id","tier_name":"test-tier-name","total_months_subscribed":12,"is_removal":false}',
                new RoleSubscriptionData(
                    role_subscription_listing_id: 'test-id',
                    tier_name: 'test-tier-name',
                    total_months_subscribed: 12,
                    is_removal: false
                )
            ]
        ];
    }

    /**
     * @param string $subject
     * @param RoleSubscriptionData $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, RoleSubscriptionData $expected): void
    {
        self::testDeserialization($subject, $expected, RoleSubscriptionData::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\RoleTags;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\RoleTags
 */
final class RoleTagsTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param RoleTags $expected
     * @param RoleTags $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->bot_id, $actual->bot_id);
        self::assertSame($expected->integration_id, $actual->integration_id);
        self::assertSame($expected->premium_subscriber, $actual->premium_subscriber);
        self::assertSame($expected->subscription_listing_id, $actual->subscription_listing_id);
        self::assertSame($expected->available_for_purchase, $actual->available_for_purchase);
        self::assertSame($expected->guild_connections, $actual->guild_connections);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        return [
            ['{}', new RoleTags(premium_subscriber: false, available_for_purchase: false, guild_connections: false)],
            [
                '{"bot_id":"test-bot-id"}',
                new RoleTags(
                    bot_id: 'test-bot-id',
                    premium_subscriber: false,
                    available_for_purchase: false,
                    guild_connections: false
                )
            ],
            [
                '{"integration_id":"test-integration-id"}',
                new RoleTags(
                    integration_id: 'test-integration-id',
                    premium_subscriber: false,
                    available_for_purchase: false,
                    guild_connections: false
                )
            ],
            [
                '{"premium_subscriber":null}',
                new RoleTags(premium_subscriber: true, available_for_purchase: false, guild_connections: false)
            ],
            [
                '{"subscription_listing_id":"test-subscription-listing-id"}',
                new RoleTags(
                    premium_subscriber: false,
                    subscription_listing_id: 'test-subscription-listing-id',
                    available_for_purchase: false,
                    guild_connections: false
                )
            ],
            [
                '{"available_for_purchase":null}',
                new RoleTags(premium_subscriber: false, available_for_purchase: true, guild_connections: false)
            ],
            [
                '{"guild_connections":null}',
                new RoleTags(premium_subscriber: false, available_for_purchase: false, guild_connections: true)
            ],
            [
                '{"bot_id":"test-bot-id","integration_id":"test-integration-id"}',
                new RoleTags(
                    bot_id: 'test-bot-id',
                    integration_id: 'test-integration-id',
                    premium_subscriber: false,
                    available_for_purchase: false,
                    guild_connections: false
                )
            ],
            [
                '{"bot_id":"test-bot-id","subscription_listing_id":"test-subscription-listing-id"}',
                new RoleTags(
                    bot_id: 'test-bot-id',
                    premium_subscriber: false,
                    subscription_listing_id: 'test-subscription-listing-id',
                    available_for_purchase: false,
                    guild_connections: false
                )
            ],
            [
                '{"integration_id":"test-integration-id","subscription_listing_id":"test-subscription-listing-id"}',
                new RoleTags(
                    integration_id: 'test-integration-id',
                    premium_subscriber: false,
                    subscription_listing_id: 'test-subscription-listing-id',
                    available_for_purchase: false,
                    guild_connections: false
                )
            ],
            [
                '{"bot_id":"test-bot-id","integration_id":"test-integration-id","subscription_listing_id":"test-subscription-listing-id"}',
                new RoleTags(
                    bot_id: 'test-bot-id',
                    integration_id: 'test-integration-id',
                    premium_subscriber: false,
                    subscription_listing_id: 'test-subscription-listing-id',
                    available_for_purchase: false,
                    guild_connections: false
                )
            ]
        ];
    }

    /**
     * @param string $subject
     * @param RoleTags $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, RoleTags $expected): void
    {
        self::testDeserialization($subject, $expected, RoleTags::class);
    }
}

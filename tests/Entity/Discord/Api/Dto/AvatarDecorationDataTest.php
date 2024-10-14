<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\AvatarDecorationData;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\AvatarDecorationData
 */
final class AvatarDecorationDataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param AvatarDecorationData $expected
     * @param AvatarDecorationData $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->asset, $actual->asset);
        self::assertSame($expected->sku_id, $actual->sku_id);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        return [
            [
                '{"asset":"test-asset","sku_id":"test-sku-id"}',
                new AvatarDecorationData(asset: 'test-asset', sku_id: 'test-sku-id')
            ]
        ];
    }

    /**
     * @param string $subject
     * @param AvatarDecorationData $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, AvatarDecorationData $expected): void
    {
        self::testDeserialization($subject, $expected, AvatarDecorationData::class);
    }
}

<?php

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\AvatarDecorationData;
use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\PremiumTypes;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\User
 */
class UserTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param User $expected
     * @param User $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->username, $actual->username);
        self::assertSame($expected->discriminator, $actual->discriminator);
        self::assertSame($expected->global_name, $actual->global_name);
        self::assertSame($expected->avatar, $actual->avatar);
        self::assertSame($expected->bot, $actual->bot);
        self::assertSame($expected->system, $actual->system);
        self::assertSame($expected->mfa_enabled, $actual->mfa_enabled);
        self::assertSame($expected->banner, $actual->banner);
        self::assertSame($expected->accent_color, $actual->accent_color);
        self::assertSame($expected->locale, $actual->locale);
        self::assertSame($expected->verified, $actual->verified);
        self::assertSame($expected->email, $actual->email);
        self::assertSame($expected->flags, $actual->flags);
        self::assertSame($expected->premium_type, $actual->premium_type);
        self::assertSame($expected->public_flags, $actual->public_flags);

        if (isset($expected->avatar_decoration_data)) {
            AvatarDecorationDataTest::assertDeepSame($expected->avatar_decoration_data, $actual->avatar_decoration_data);
            return;
        }

        self::assertNull($actual->avatar_decoration_data);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","username":"test-username","discriminator":"test-discriminator","global_name":%s,"avatar":%s%s}';

        $baseExpected = new User(
            id: 'test-id',
            username: 'test-username',
            discriminator: 'test-discriminator',
            global_name: null,
            avatar: null
        );

        $withPremiumType = clone $baseExpected;
        $withPremiumType->premium_type = PremiumTypes::Nitro;

        $withAvatarDecorationData = clone $baseExpected;
        $withAvatarDecorationData->avatar_decoration_data = new AvatarDecorationData(
            asset: 'test-asset',
            sku_id: 'test-sku-id'
        );

        return [
            [sprintf($subjectTemplate, 'null', 'null', ''), $baseExpected],
            [sprintf($subjectTemplate, 'null', 'null', ',"premium_type":2'), $withPremiumType],
            [
                sprintf(
                    $subjectTemplate,
                    'null',
                    'null',
                    ',"avatar_decoration_data":{"asset":"test-asset","sku_id":"test-sku-id"}'
                ),
                $withAvatarDecorationData
            ]
        ];
    }

    /**
     * @param string $subject
     * @param User $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, User $expected): void
    {
        self::testDeserialization($subject, $expected, User::class);
    }
}

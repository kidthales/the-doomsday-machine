<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\PremiumType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\User
 */
final class UserTest extends AbstractSerializableSubjectTestCase
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

        $data = [];

        foreach (AvatarDecorationDataTest::provider_deserialization() as [$avatarDecorationDataTemplate, $avatarDecorationDataExpected]) {
            foreach (PremiumType::cases() as $premium) {
                $data[] = [
                    sprintf($subjectTemplate, 'null', 'null', ',"banner":null,"accent_color":null,"email":null,"premium_type":' . $premium->value . ',"avatar_decoration_data":' . $avatarDecorationDataTemplate),
                    new User(
                        id: 'test-id',
                        username: 'test-username',
                        discriminator: 'test-discriminator',
                        global_name: null,
                        avatar: null,
                        premium_type: $premium,
                        avatar_decoration_data: $avatarDecorationDataExpected
                    )
                ];
            }
        }

        return [
            [
                sprintf($subjectTemplate, 'null', 'null', ',"banner":null,"accent_color":null,"email":null,"avatar_decoration_data":null'),
                new User(
                    id: 'test-id',
                    username: 'test-username',
                    discriminator: 'test-discriminator',
                    global_name: null,
                    avatar: null
                )
            ],
            ...$data
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

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $data = [];

        foreach (self::provider_deserialization() as [$template, $expected]) {
            $data[] = [$expected, $template];
        }

        return [
            [
                new User(
                    id: 'test-id',
                    username: 'test-username',
                    discriminator: 'test-discriminator',
                    global_name: null,
                    avatar: null,
                    bot: true,
                    system: true,
                    mfa_enabled: true,
                    banner: 'test-banner',
                    accent_color: 0,
                    locale: 'test-locale',
                    verified: true,
                    flags: 6,
                    public_flags: 7
                ),
                '{"id":"test-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null,"bot":true,"system":true,"mfa_enabled":true,"banner":"test-banner","accent_color":0,"locale":"test-locale","verified":true,"email":null,"flags":6,"public_flags":7,"avatar_decoration_data":null}'
            ],
            ...$data
        ];
    }

    /**
     * @param User $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(User $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}

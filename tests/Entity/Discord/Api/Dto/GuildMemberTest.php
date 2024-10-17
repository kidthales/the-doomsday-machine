<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\AvatarDecorationData;
use App\Entity\Discord\Api\Dto\GuildMember;
use App\Entity\Discord\Api\Dto\User;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\GuildMember
 */
final class GuildMemberTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param GuildMember $expected
     * @param GuildMember $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame(count($expected->roles), count($actual->roles));

        for ($i = 0; $i < count($expected->roles); ++$i) {
            self::assertSame($expected->roles[$i], $actual->roles[$i]);
        }

        self::assertSame($expected->joined_at, $expected->joined_at);
        self::assertSame($expected->deaf, $actual->deaf);
        self::assertSame($expected->mute, $actual->mute);
        self::assertSame($expected->flags, $actual->flags);

        if (isset($expected->user)) {
            UserTest::assertDeepSame($expected->user, $actual->user);
        } else {
            self::assertNull($actual->user);
        }

        self::assertSame($expected->nick, $actual->nick);
        self::assertSame($expected->avatar, $actual->avatar);
        self::assertSame($expected->premium_since, $actual->premium_since);
        self::assertSame($expected->pending, $actual->pending);
        self::assertSame($expected->permissions, $actual->permissions);
        self::assertSame($expected->communication_disabled_until, $actual->communication_disabled_until);

        if (isset($expected->avatar_decoration_data)) {
            AvatarDecorationDataTest::assertDeepSame($expected->avatar_decoration_data, $actual->avatar_decoration_data);
        } else {
            self::assertNull($actual->avatar_decoration_data);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s"roles":["test-role-id"],"joined_at":"test-joined-at",%s"deaf":true,"mute":false,"flags":0%s}';

        $baseExpected = new GuildMember(
            roles: ['test-role-id'],
            joined_at: 'test-joined-at',
            deaf: true,
            mute: false,
            flags: 0
        );

        $withUser = clone $baseExpected;
        $withUser->user = new User(
            id: 'test-id',
            username: 'test-username',
            discriminator: 'test-discriminator',
            global_name: null,
            avatar: null
        );

        $withAvatarDecorationData = clone $baseExpected;
        $withAvatarDecorationData->avatar_decoration_data = new AvatarDecorationData(
            asset: 'test-asset',
            sku_id: 'test-sku-id'
        );

        return [
            [sprintf($subjectTemplate, '', '', ''), $baseExpected],
            [
                sprintf(
                    $subjectTemplate,
                    '"user":{"id":"test-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null},',
                    '',
                    ''
                ),
                $withUser
            ],
            [
                sprintf(
                    $subjectTemplate,
                    '',
                    '',
                    ',"avatar_decoration_data":{"asset":"test-asset","sku_id":"test-sku-id"}'
                ),
                $withAvatarDecorationData
            ]
        ];
    }

    /**
     * @param string $subject
     * @param GuildMember $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, GuildMember $expected): void
    {
        self::testDeserialization($subject, $expected, GuildMember::class);
    }
}

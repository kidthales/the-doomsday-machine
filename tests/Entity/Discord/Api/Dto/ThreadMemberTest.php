<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\GuildMember;
use App\Entity\Discord\Api\Dto\ThreadMember;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ThreadMember
 */
final class ThreadMemberTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ThreadMember $expected
     * @param ThreadMember $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->join_timestamp, $actual->join_timestamp);
        self::assertSame($expected->flags, $actual->flags);
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->user_id, $actual->user_id);

        if (isset($expected->member)) {
            GuildMemberTest::assertDeepSame($expected->member, $actual->member);
        } else {
            self::assertNull($actual->member);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s"join_timestamp":"test-join-timestamp","flags":13%s}';

        $baseExpected = new ThreadMember(join_timestamp: 'test-join-timestamp', flags: 13);

        $withId = clone $baseExpected;
        $withId->id = 'test-id';

        $withUserId = clone $baseExpected;
        $withUserId->user_id = 'test-user-id';

        $withMember = clone $baseExpected;
        $withMember->member = new GuildMember(
            roles: ['test-role-id'],
            joined_at: 'test-joined-at',
            deaf: true,
            mute: false,
            flags: 4
        );

        return [
            [sprintf($subjectTemplate, '', ''), $baseExpected],
            [sprintf($subjectTemplate, '"id":"test-id",', ''), $withId],
            [sprintf($subjectTemplate, '"user_id":"test-user-id",', ''), $withUserId],
            [
                sprintf(
                    $subjectTemplate,
                    '',
                    ',"member":{"roles":["test-role-id"],"joined_at":"test-joined-at","deaf":true,"mute":false,"flags":4}'
                ),
                $withMember
            ]
        ];
    }

    /**
     * @param string $subject
     * @param ThreadMember $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ThreadMember $expected): void
    {
        self::testDeserialization($subject, $expected, ThreadMember::class);
    }
}

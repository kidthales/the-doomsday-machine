<?php

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\TeamMember;
use App\Entity\Discord\Api\Enumeration\MembershipState;
use App\Entity\Discord\Api\Enumeration\TeamMemberRole;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\TeamMember
 */
class TeamMemberTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param TeamMember $expected
     * @param TeamMember $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->membership_state, $actual->membership_state);
        self::assertSame($expected->team_id, $actual->team_id);

        UserTest::assertDeepSame($expected->user, $actual->user);

        self::assertSame($expected->role, $actual->role);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"membership_state":%s,"team_id":"test-team-id","user":%s,"role":"%s"}';

        $data = [];

        foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
            foreach (MembershipState::cases() as $membershipState) {
                foreach (TeamMemberRole::cases() as $role) {
                    $data[] = [
                        sprintf($subjectTemplate, $membershipState->value, $userTemplate, $role->value),
                        new TeamMember(
                            membership_state: $membershipState,
                            team_id: 'test-team-id',
                            user: $userExpected,
                            role: $role
                        )
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param TeamMember $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, TeamMember $expected): void
    {
        self::testDeserialization($subject, $expected, TeamMember::class);
    }
}

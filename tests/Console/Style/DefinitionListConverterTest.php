<?php

namespace App\Tests\Console\Style;

use App\Console\Style\DefinitionListConverter;
use App\Entity\Discord\Api\Dto\Team;
use App\Entity\Discord\Api\Dto\TeamMember;
use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\MembershipState;
use App\Entity\Discord\Api\Enumeration\TeamMemberRole;
use ArrayObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Console\Style\DefinitionListConverter
 */
final class DefinitionListConverterTest extends KernelTestCase
{
    /**
     * @return array
     */
    public static function provider_convert(): array
    {
        return [
            [null, [null]],
            [[], []],
            [new ArrayObject(), []],
            [
                ['test-key-1' => 'test-value-1', 'test-key-2' => 'test-value-2'],
                [['test-key-1' => 'test-value-1'], ['test-key-2' => 'test-value-2']]
            ],
            [
                new ArrayObject(['test-key-1' => 'test-value-1', 'test-key-2' => 'test-value-2']),
                [['test-key-1' => 'test-value-1'], ['test-key-2' => 'test-value-2']]
            ],
            [
                new Team(
                    icon: 'test-icon',
                    id: 'test-id',
                    members: [
                        new TeamMember(
                            membership_state: MembershipState::ACCEPTED,
                            team_id: 'test-team-id',
                            user: new User(
                                id: 'test-user-id',
                                username: 'test-username',
                                discriminator: 'test-discriminator',
                                global_name: null,
                                avatar: null
                            ),
                            role: TeamMemberRole::developer
                        )
                    ],
                    name: 'test-name',
                    owner_user_id: 'test-owner-user-id'
                ),
                [
                    ['icon' => 'test-icon'],
                    ['id' => 'test-id'],
                    ['members.0.membership_state' => MembershipState::ACCEPTED->value],
                    ['members.0.team_id' => 'test-team-id'],
                    ['members.0.user.id' => 'test-user-id'],
                    ['members.0.user.username' => 'test-username'],
                    ['members.0.user.discriminator' => 'test-discriminator'],
                    ['members.0.user.global_name' => null],
                    ['members.0.user.avatar' => null],
                    ['members.0.user.bot' => null],
                    ['members.0.user.system' => null],
                    ['members.0.user.mfa_enabled' => null],
                    ['members.0.user.banner' => null],
                    ['members.0.user.accent_color' => null],
                    ['members.0.user.locale' => null],
                    ['members.0.user.verified' => null],
                    ['members.0.user.email' => null],
                    ['members.0.user.flags' => null],
                    ['members.0.user.premium_type' => null],
                    ['members.0.user.public_flags' => null],
                    ['members.0.user.avatar_decoration_data' => null],
                    ['members.0.role' => 'developer'],
                    ['name' => 'test-name'],
                    ['owner_user_id' => 'test-owner-user-id']
                ],
            ]
        ];
    }

    /**
     * @param mixed $subject
     * @param array $expected
     * @return void
     * @dataProvider provider_convert
     */
    public function test_convert(mixed $subject, array $expected): void
    {
        self::bootKernel();

        $converter = self::getContainer()->get(DefinitionListConverter::class);

        $actual = $converter->convert($subject);

        self::assertSame(count($expected), count($actual));

        foreach ($expected as $key => $value) {
            self::assertSame($value, $actual[$key]);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Team;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Team
 */
final class TeamTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Team $expected
     * @param Team $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->icon, $actual->icon);
        self::assertSame($expected->id, $actual->id);

        self::assertSame(count($expected->members), count($actual->members));

        for ($i = 0; $i < count($expected->members); ++$i) {
            TeamMemberTest::assertDeepSame($expected->members[$i], $actual->members[$i]);
        }

        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->owner_user_id, $actual->owner_user_id);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"icon":%s,"id":"test-id","members":[%s],"name":"test-name","owner_user_id":"test-owner-user-id"}';

        $teamMemberTemplates = [];
        $teamMembersExpected = [];

        foreach (TeamMemberTest::provider_deserialization() as [$teamMemberTemplate, $teamMemberExpected]) {
            $teamMemberTemplates[] = $teamMemberTemplate;
            $teamMembersExpected[] = $teamMemberExpected;
        }

        return [
            [
                sprintf($subjectTemplate, 'null', ''),
                new Team(icon: null, id: 'test-id', members: [], name: 'test-name', owner_user_id: 'test-owner-user-id')
            ],
            [
                sprintf($subjectTemplate, '"test-icon"', implode(',', $teamMemberTemplates)),
                new Team(
                    icon: 'test-icon',
                    id: 'test-id',
                    members: $teamMembersExpected,
                    name: 'test-name',
                    owner_user_id: 'test-owner-user-id'
                )
            ]
        ];
    }

    /**
     * @param string $subject
     * @param Team $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Team $expected): void
    {
        self::testDeserialization($subject, $expected, Team::class);
    }
}

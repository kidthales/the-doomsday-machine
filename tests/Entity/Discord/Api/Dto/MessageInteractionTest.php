<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\MessageInteraction;
use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\InteractionType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageInteraction
 */
final class MessageInteractionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageInteraction $expected
     * @param MessageInteraction $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->name, $actual->name);

        UserTest::assertDeepSame($expected->user, $actual->user);

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
        $subjectTemplate = '{"id":"test-id","type":%s,"name":"test-name","user":%s%s}';

        $data = [];

        foreach (InteractionType::cases() as $type) {
            foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
                foreach (GuildMemberTest::provider_deserialization() as [$gmTemplate, $gmExpected]) {
                    $data[] = [
                        sprintf($subjectTemplate, $type->value, $userTemplate, ',"member":' . $gmTemplate),
                        new MessageInteraction(
                            id: 'test-id',
                            type: $type,
                            name: 'test-name',
                            user: $userExpected,
                            member: $gmExpected
                        )
                    ];
                }
            }
        }

        return [
            [
                sprintf($subjectTemplate, InteractionType::PING->value, '{"id":"test-user-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null}', ''),
                new MessageInteraction(
                    id: 'test-id',
                    type: InteractionType::PING,
                    name: 'test-name',
                    user: new User(
                        id: 'test-user-id',
                        username: 'test-username',
                        discriminator: 'test-discriminator',
                        global_name: null,
                        avatar: null
                    )
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param MessageInteraction $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageInteraction $expected): void
    {
        self::testDeserialization($subject, $expected, MessageInteraction::class);
    }
}

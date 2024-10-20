<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\MessageInteractionMetadata;
use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\InteractionType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageInteractionMetadata
 */
final class MessageInteractionMetadataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageInteractionMetadata $expected
     * @param MessageInteractionMetadata $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->type, $actual->type);

        UserTest::assertDeepSame($expected->user, $actual->user);

        self::assertSame(count($expected->authorizing_integration_owners), count($actual->authorizing_integration_owners));

        for ($i = 0; $i < count($expected->authorizing_integration_owners); ++$i) {
            self::assertSame($expected->authorizing_integration_owners[$i], $actual->authorizing_integration_owners[$i]);
        }

        self::assertSame($expected->original_response_message_id, $actual->original_response_message_id);
        self::assertSame($expected->interacted_message_id, $actual->interacted_message_id);

        if (isset($expected->triggering_interaction_metadata)) {
            self::assertDeepSame($expected->triggering_interaction_metadata, $actual->triggering_interaction_metadata);
        } else {
            self::assertNull($actual->triggering_interaction_metadata);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","type":%s,"user":%s,"authorizing_integration_owners":[%s]%s}';

        $data = [];

        foreach (InteractionType::cases() as $type) {
            foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
                $data[] = [
                    sprintf($subjectTemplate, $type->value, $userTemplate, '"' . implode('","', ['test-1', 'test-2']) . '"', ',"original_response_message_id":"test-id","interacted_message_id":"test-id"'),
                    new MessageInteractionMetadata(
                        id: 'test-id',
                        type: $type,
                        user: $userExpected,
                        authorizing_integration_owners: ['test-1', 'test-2'],
                        original_response_message_id: 'test-id',
                        interacted_message_id: 'test-id'
                    )
                ];
            }
        }

        return [
            [
                sprintf($subjectTemplate, InteractionType::PING->value, '{"id":"test-user-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null}', '', ''),
                new MessageInteractionMetadata(
                    id: 'test-id',
                    type: InteractionType::PING,
                    user: new User(id: 'test-user-id', username: 'test-username', discriminator: 'test-discriminator', global_name: null, avatar: null),
                    authorizing_integration_owners: []
                )
            ],
            ...$data,
            [
                sprintf($subjectTemplate, InteractionType::PING->value, '{"id":"test-user-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null}', '', ',"triggering_interaction_metadata":' . sprintf($subjectTemplate, InteractionType::PING->value, '{"id":"test-user-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null}', '', '')),
                new MessageInteractionMetadata(
                    id: 'test-id',
                    type: InteractionType::PING,
                    user: new User(id: 'test-user-id', username: 'test-username', discriminator: 'test-discriminator', global_name: null, avatar: null),
                    authorizing_integration_owners: [],
                    triggering_interaction_metadata: new MessageInteractionMetadata(
                        id: 'test-id',
                        type: InteractionType::PING,
                        user: new User(id: 'test-user-id', username: 'test-username', discriminator: 'test-discriminator', global_name: null, avatar: null),
                        authorizing_integration_owners: []
                    )
                )
            ]
        ];
    }

    /**
     * @param string $subject
     * @param MessageInteractionMetadata $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageInteractionMetadata $expected): void
    {
        self::testDeserialization($subject, $expected, MessageInteractionMetadata::class);
    }
}

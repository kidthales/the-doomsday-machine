<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\MessageReference;
use App\Entity\Discord\Api\Enumeration\MessageReferenceType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageReference
 */
final class MessageReferenceTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageReference $expected
     * @param MessageReference $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->message_id, $actual->message_id);
        self::assertSame($expected->channel_id, $actual->channel_id);
        self::assertSame($expected->guild_id, $actual->guild_id);
        self::assertSame($expected->fail_if_not_exists, $actual->fail_if_not_exists);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s}';

        $data = [];

        foreach (MessageReferenceType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, '"type":' . $type->value . ',"message_id":"test-message-id","channel_id":"test-channel-id","guild_id":"test-guild-id","fail_if_not_exists":true'),
                new MessageReference(
                    type: $type,
                    message_id: 'test-message-id',
                    channel_id: 'test-channel-id',
                    guild_id: 'test-guild-id',
                    fail_if_not_exists: true
                )
            ];
        }

        return [
            [sprintf($subjectTemplate, ''), new MessageReference()],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param MessageReference $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageReference $expected): void
    {
        self::testDeserialization($subject, $expected, MessageReference::class);
    }
}

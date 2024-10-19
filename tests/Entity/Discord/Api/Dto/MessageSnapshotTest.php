<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Message;
use App\Entity\Discord\Api\Dto\MessageSnapshot;
use App\Entity\Discord\Api\Enumeration\MessageType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageSnapshot
 */
final class MessageSnapshotTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageSnapshot $expected
     * @param MessageSnapshot $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        MessageTest::assertDeepSame($expected->message, $actual->message);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"message":{"id":"test-id","channel_id":"test-channel-id","author":%s,"content":"test-content","timestamp":"test-timestamp","edited_timestamp":null,"tts":false,"mention_everyone":false,"mentions":[%s],"mention_roles":["test-1","test-2"],"attachments":[%s],"embeds":[%s],"pinned":false,"type":%s}}';

        $userTemplates = [];
        $usersExpected = [];

        foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
            $userTemplates[] = $userTemplate;
            $usersExpected[] = $userExpected;
        }

        $attachmentTemplates = [];
        $attachmentsExpected = [];

        foreach (AttachmentTest::provider_deserialization() as [$attachmentTemplate, $attachmentExpected]) {
            $attachmentTemplates[] = $attachmentTemplate;
            $attachmentsExpected[] = $attachmentExpected;
        }

        $embedTemplates = [];
        $embedsExpected = [];

        foreach (EmbedTest::provider_deserialization() as [$embedTemplate, $embedExpected]) {
            $embedTemplates[] = $embedTemplate;
            $embedsExpected[] = $embedExpected;
        }

        $data = [];

        foreach (MessageType::cases() as $type) {
            $authorIx = array_rand($usersExpected);

            $data[] = [
                sprintf($subjectTemplate, $userTemplates[$authorIx], implode(',', $userTemplates), implode(',', $attachmentTemplates), implode(',', $embedTemplates), $type->value),
                new MessageSnapshot(
                    message: new Message(
                        id: 'test-id',
                        channel_id: 'test-channel-id',
                        author: $usersExpected[$authorIx],
                        content: 'test-content',
                        timestamp: 'test-timestamp',
                        edited_timestamp: null,
                        tts: false,
                        mention_everyone: false,
                        mentions: $usersExpected,
                        mention_roles: ['test-1', 'test-2'],
                        attachments: $attachmentsExpected,
                        embeds: $embedsExpected,
                        pinned: false,
                        type: $type
                    )
                )
            ];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param MessageSnapshot $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageSnapshot $expected): void
    {
        self::testDeserialization($subject, $expected, MessageSnapshot::class);
    }
}

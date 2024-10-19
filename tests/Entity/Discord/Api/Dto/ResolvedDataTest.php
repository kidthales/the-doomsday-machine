<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Message;
use App\Entity\Discord\Api\Dto\ResolvedData;
use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\MessageType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ResolvedData
 */
final class ResolvedDataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ResolvedData $expected
     * @param ResolvedData $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        if (isset($expected->users)) {
            self::assertSame(count($expected->users), count($actual->users));

            foreach ($expected->users as $id => $user) {
                UserTest::assertDeepSame($user, $actual->users[$id]);
            }
        } else {
            self::assertNull($actual->users);
        }

        if (isset($expected->members)) {
            self::assertSame(count($expected->members), count($actual->members));

            foreach ($expected->members as $id => $member) {
                GuildMemberTest::assertDeepSame($member, $actual->members[$id]);
            }
        } else {
            self::assertNull($actual->members);
        }

        if (isset($expected->roles)) {
            self::assertSame(count($expected->roles), count($actual->roles));

            foreach ($expected->roles as $id => $role) {
                RoleTest::assertDeepSame($role, $actual->roles[$id]);
            }
        } else {
            self::assertNull($actual->roles);
        }

        if (isset($expected->channels)) {
            self::assertSame(count($expected->channels), count($actual->channels));

            foreach ($expected->channels as $id => $channel) {
                ChannelTest::assertDeepSame($channel, $actual->channels[$id]);
            }
        } else {
            self::assertNull($actual->channels);
        }

        if (isset($expected->messages)) {
            self::assertSame(count($expected->messages), count($actual->messages));

            foreach ($expected->messages as $id => $message) {
                MessageTest::assertDeepSame($message, $actual->messages[$id]);
            }
        } else {
            self::assertNull($actual->messages);
        }

        if (isset($expected->attachments)) {
            self::assertSame(count($expected->attachments), count($actual->attachments));

            foreach ($expected->attachments as $id => $attachment) {
                AttachmentTest::assertDeepSame($attachment, $actual->attachments[$id]);
            }
        } else {
            self::assertNull($actual->attachments);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s}';

        $userTemplates = [];
        $usersExpected = [];

        foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
            $userTemplates[] = $userTemplate;
            $usersExpected[] = $userExpected;
        }

        $memberTemplates = [];
        $membersExpected = [];

        foreach (GuildMemberTest::provider_deserialization() as [$memberTemplate, $memberExpected]) {
            $memberTemplates[] = $memberTemplate;
            $membersExpected[] = $memberExpected;
        }

        $roleTemplates = [];
        $rolesExpected = [];

        foreach (RoleTest::provider_deserialization() as [$roleTemplate, $roleExpected]) {
            $roleTemplates[] = $roleTemplate;
            $rolesExpected[] = $roleExpected;
        }

        $channelTemplates = [];
        $channelsExpected = [];

        foreach (ChannelTest::provider_deserialization() as [$channelTemplate, $channelExpected]) {
            $channelTemplates[] = $channelTemplate;
            $channelsExpected[] = $channelExpected;
        }

        $messageTemplate = '{"id":"test-id","channel_id":"test-channel-id","author":{"id":"test-id","username":"test-username","discriminator":"test-discriminator","global_name":null,"avatar":null},"content":"test-content","timestamp":"test-timestamp","edited_timestamp":null,"tts":false,"mention_everyone":false,"mentions":[],"mention_roles":["test-1","test-2"],"attachments":[],"embeds":[],"pinned":false,"type":0}';
        $messageExpected = new Message(
            id: 'test-id',
            channel_id: 'test-channel-id',
            author: new User(
                id: 'test-id',
                username: 'test-username',
                discriminator: 'test-discriminator',
                global_name: null,
                avatar: null
            ),
            content: 'test-content',
            timestamp: 'test-timestamp',
            edited_timestamp: null,
            tts: false,
            mention_everyone: false,
            mentions: [],
            mention_roles: ["test-1","test-2"],
            attachments: [],
            embeds: [],
            pinned: false,
            type: MessageType::DEFAULT,
        );

        $attachmentTemplates = [];
        $attachmentsExpected = [];

        foreach (AttachmentTest::provider_deserialization() as [$attachmentTemplate, $attachmentExpected]) {
            $attachmentTemplates[] = $attachmentTemplate;
            $attachmentsExpected[] = $attachmentExpected;
        }

        return [
            [sprintf($subjectTemplate, ''), new ResolvedData()],
            [
                sprintf($subjectTemplate, '"users":[' . implode(',', $userTemplates) . ']'),
                new ResolvedData(users: $usersExpected)
            ],
            [
                sprintf($subjectTemplate, '"members":[' . implode(',', $memberTemplates) . ']'),
                new ResolvedData(members: $membersExpected)
            ],
            [
                sprintf($subjectTemplate, '"roles":[' . implode(',', $roleTemplates) . ']'),
                new ResolvedData(roles: $rolesExpected)
            ],
            [
                sprintf($subjectTemplate, '"channels":[' . implode(',', $channelTemplates) . ']'),
                new ResolvedData(channels: $channelsExpected)
            ],
            [
                sprintf($subjectTemplate, '"messages":[' . implode(',', [$messageTemplate, $messageTemplate]) . ']'),
                new ResolvedData(messages: [$messageExpected, $messageExpected])
            ],
            [
                sprintf($subjectTemplate, '"attachments":[' . implode(',', $attachmentTemplates) . ']'),
                new ResolvedData(attachments: $attachmentsExpected)
            ]
        ];
    }

    /**
     * @param string $subject
     * @param ResolvedData $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ResolvedData $expected): void
    {
        self::testDeserialization($subject, $expected, ResolvedData::class);
    }
}

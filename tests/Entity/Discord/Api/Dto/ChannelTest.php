<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Channel;
use App\Entity\Discord\Api\Enumeration\ChannelType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Channel
 */
final class ChannelTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Channel $expected
     * @param Channel $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->guild_id, $actual->guild_id);
        self::assertSame($expected->position, $actual->position);

        if (isset($expected->permission_overwrites)) {
            self::assertSame(count($expected->permission_overwrites), count($actual->permission_overwrites));

            for ($i = 0; $i < count($expected->permission_overwrites); ++$i) {
                OverwriteTest::assertDeepSame($expected->permission_overwrites[$i], $actual->permission_overwrites[$i]);
            }
        } else {
            self::assertNull($actual->permission_overwrites);
        }

        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->topic, $actual->topic);
        self::assertSame($expected->nsfw, $actual->nsfw);
        self::assertSame($expected->last_message_id, $actual->last_message_id);
        self::assertSame($expected->bitrate, $actual->bitrate);
        self::assertSame($expected->user_limit, $actual->user_limit);
        self::assertSame($expected->rate_limit_per_user, $actual->rate_limit_per_user);

        if (isset($expected->recipients)) {
            self::assertSame(count($expected->recipients), count($actual->recipients));

            for ($i = 0; $i < count($expected->recipients); ++$i) {
                UserTest::assertDeepSame($expected->recipients[$i], $actual->recipients[$i]);
            }
        } else {
            self::assertNull($actual->recipients);
        }

        self::assertSame($expected->icon, $actual->icon);
        self::assertSame($expected->owner_id, $actual->owner_id);
        self::assertSame($expected->application_id, $actual->application_id);
        self::assertSame($expected->managed, $actual->managed);
        self::assertSame($expected->parent_id, $actual->parent_id);
        self::assertSame($expected->last_pin_timestamp, $actual->last_pin_timestamp);
        self::assertSame($expected->rtc_region, $actual->rtc_region);
        self::assertSame($expected->video_quality_mode, $actual->video_quality_mode);
        self::assertSame($expected->message_count, $actual->message_count);
        self::assertSame($expected->member_count, $actual->member_count);

        if (isset($expected->thread_metadata)) {
            ThreadMetadataTest::assertDeepSame($expected->thread_metadata, $actual->thread_metadata);
        } else {
            self::assertNull($actual->thread_metadata);
        }

        if (isset($expected->member)) {
            ThreadMemberTest::assertDeepSame($expected->member, $actual->member);
        } else {
            self::assertNull($actual->member);
        }

        self::assertSame($expected->default_auto_archive_duration, $actual->default_auto_archive_duration);
        self::assertSame($expected->permissions, $actual->permissions);
        self::assertSame($expected->flags, $actual->flags);

        if (isset($expected->default_reaction_emoji)) {
            DefaultReactionTest::assertDeepSame($expected->default_reaction_emoji, $actual->default_reaction_emoji);
        } else {
            self::assertNull($actual->default_reaction_emoji);
        }

        self::assertSame($expected->default_thread_rate_limit_per_user, $actual->default_thread_rate_limit_per_user);
        self::assertSame($expected->default_sort_order, $actual->default_sort_order);
        self::assertSame($expected->default_forum_layout, $actual->default_forum_layout);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","type":%s%s}';

        $overwriteTemplates = [];
        $overwritesExpected = [];

        foreach (OverwriteTest::provider_deserialization() as [$overwriteTemplate, $overwriteExpected]) {
            $overwriteTemplates[] = $overwriteTemplate;
            $overwritesExpected[] = $overwriteExpected;
        }

        $userTemplates = [];
        $usersExpected = [];

        foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
            $userTemplates[] = $userTemplate;
            $usersExpected[] = $userExpected;
        }

        $data = [];

        foreach (ChannelType::cases() as $channelType) {
            $data[] = [
                sprintf($subjectTemplate, $channelType->value, ',"permission_overwrites":[' . implode(',', $overwriteTemplates) . '],"recipients":[' . implode(',', $userTemplates) . ']'),
                new Channel(
                    id: 'test-id',
                    type: $channelType,
                    permission_overwrites: $overwritesExpected,
                    recipients: $usersExpected
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, ChannelType::GUILD_TEXT->value, ''),
                new Channel(id: 'test-id', type: ChannelType::GUILD_TEXT)
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param Channel $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Channel $expected): void
    {
        self::testDeserialization($subject, $expected, Channel::class);
    }
}

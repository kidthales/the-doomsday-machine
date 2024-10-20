<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ActionRowComponent;
use App\Entity\Discord\Api\Dto\ButtonComponent;
use App\Entity\Discord\Api\Dto\Message;
use App\Entity\Discord\Api\Dto\SelectMenuComponent;
use App\Entity\Discord\Api\Dto\TextInputComponent;
use App\Entity\Discord\Api\Enumeration\MessageType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Message
 */
final class MessageTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Message $expected
     * @param Message $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->channel_id, $actual->channel_id);

        UserTest::assertDeepSame($expected->author, $actual->author);

        self::assertSame($expected->content, $actual->content);
        self::assertSame($expected->timestamp, $actual->timestamp);
        self::assertSame($expected->edited_timestamp, $actual->edited_timestamp);
        self::assertSame($expected->tts, $actual->tts);
        self::assertSame($expected->mention_everyone, $actual->mention_everyone);

        self::assertSame(count($expected->mentions), count($actual->mentions));

        for ($i = 0; $i < count($expected->mentions); ++$i) {
            UserTest::assertDeepSame($expected->mentions[$i], $actual->mentions[$i]);
        }

        self::assertSame(count($expected->mention_roles), count($actual->mention_roles));

        for ($i = 0; $i < count($expected->mention_roles); ++$i) {
            self::assertSame($expected->mention_roles[$i], $actual->mention_roles[$i]);
        }

        self::assertSame(count($expected->attachments), count($actual->attachments));

        for ($i = 0; $i < count($expected->attachments); ++$i) {
            AttachmentTest::assertDeepSame($expected->attachments[$i], $actual->attachments[$i]);
        }

        self::assertSame(count($expected->embeds), count($actual->embeds));

        for ($i = 0; $i < count($expected->embeds); ++$i) {
            EmbedTest::assertDeepSame($expected->embeds[$i], $actual->embeds[$i]);
        }

        self::assertSame($expected->pinned, $actual->pinned);
        self::assertSame($expected->type, $actual->type);

        if (isset($expected->mention_channels)) {
            self::assertSame(count($expected->mention_channels), count($actual->mention_channels));

            for ($i = 0; $i < count($expected->mention_channels); ++$i) {
                ChannelMentionTest::assertDeepSame($expected->mention_channels[$i], $actual->mention_channels[$i]);
            }
        } else {
            self::assertNull($actual->mention_channels);
        }

        if (isset($expected->reactions)) {
            self::assertSame(count($expected->reactions), count($actual->reactions));

            for ($i = 0; $i < count($expected->reactions); ++$i) {
                ReactionTest::assertDeepSame($expected->reactions[$i], $actual->reactions[$i]);
            }
        } else {
            self::assertNull($actual->reactions);
        }

        self::assertSame($expected->nonce, $actual->nonce);
        self::assertSame($expected->webhook_id, $actual->webhook_id);

        if (isset($expected->activity)) {
            MessageActivityTest::assertDeepSame($expected->activity, $actual->activity);
        } else {
            self::assertNull($actual->activity);
        }

        if (isset($expected->application)) {
            ApplicationTest::assertDeepSame($expected->application, $actual->application);
        } else {
            self::assertNull($actual->application);
        }

        self::assertSame($expected->application_id, $actual->application_id);
        self::assertSame($expected->flags, $actual->flags);

        if (isset($expected->message_reference)) {
            MessageReferenceTest::assertDeepSame($expected->message_reference, $actual->message_reference);
        } else {
            self::assertNull($actual->message_reference);
        }

        if (isset($expected->message_snapshots)) {
            self::assertSame(count($expected->message_snapshots), count($actual->message_snapshots));

            for ($i = 0; $i < count($expected->message_snapshots); ++$i) {
                MessageSnapshotTest::assertDeepSame($expected->message_snapshots[$i], $actual->message_snapshots[$i]);
            }
        } else {
            self::assertNull($actual->message_snapshots);
        }

        if (isset($expected->referenced_message)) {
            self::assertDeepSame($expected->referenced_message, $actual->referenced_message);
        } else {
            self::assertNull($actual->referenced_message);
        }

        if (isset($expected->interaction_metadata)) {
            MessageInteractionMetadataTest::assertDeepSame($expected->interaction_metadata, $actual->interaction_metadata);
        } else {
            self::assertNull($actual->interaction_metadata);
        }

        if (isset($expected->interaction)) {
            MessageInteractionTest::assertDeepSame($expected->interaction, $actual->interaction);
        } else {
            self::assertNull($actual->interaction);
        }

        if (isset($expected->thread)) {
            ChannelTest::assertDeepSame($expected->thread, $actual->thread);
        } else {
            self::assertNull($actual->thread);
        }

        if (isset($expected->components)) {
            self::assertSame(count($expected->components), count($actual->components));

            for ($i = 0; $i < count($expected->components); ++$i) {
                $className = get_class($expected->components[$i]);

                self::assertInstanceOf($className, $actual->components[$i]);

                switch ($className) {
                    case ActionRowComponent::class:
                        ActionRowComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                        break;
                    case ButtonComponent::class:
                        ButtonComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                        break;
                    case TextInputComponent::class:
                        TextInputComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                        break;
                    case SelectMenuComponent::class:
                        SelectMenuComponentTest::assertDeepSame($expected->components[$i], $actual->components[$i]);
                        break;
                    default:
                        self::fail('Unexpected component type: ' . $className);
                }
            }
        } else {
            self::assertNull($actual->components);
        }

        if (isset($expected->sticker_items)) {
            self::assertSame(count($expected->sticker_items), count($actual->sticker_items));

            for ($i = 0; $i < count($expected->sticker_items); ++$i) {
                StickerItemTest::assertDeepSame($expected->sticker_items[$i], $actual->sticker_items[$i]);
            }
        } else {
            self::assertNull($actual->sticker_items);
        }

        if (isset($expected->stickers)) {
            self::assertSame(count($expected->stickers), count($actual->stickers));

            for ($i = 0; $i < count($expected->stickers); ++$i) {
                StickerTest::assertDeepSame($expected->stickers[$i], $actual->stickers[$i]);
            }
        }

        self::assertSame($expected->position, $actual->position);

        if (isset($expected->role_subscription_data)) {
            RoleSubscriptionDataTest::assertDeepSame($expected->role_subscription_data, $actual->role_subscription_data);
        } else {
            self::assertNull($actual->role_subscription_data);
        }

        if (isset($expected->resolved)) {
            ResolvedDataTest::assertDeepSame($expected->resolved, $actual->resolved);
        } else {
            self::assertNull($actual->resolved);
        }

        if (isset($expected->poll)) {
            PollTest::assertDeepSame($expected->poll, $actual->poll);
        } else {
            self::assertNull($actual->poll);
        }

        if (isset($expected->call)) {
            MessageCallTest::assertDeepSame($expected->call, $actual->call);
        } else {
            self::assertNull($actual->call);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","channel_id":"test-channel-id","author":%s,"content":"test-content","timestamp":"test-timestamp","edited_timestamp":null,"tts":false,"mention_everyone":false,"mentions":[%s],"mention_roles":["test-1","test-2"],"attachments":[%s],"embeds":[%s],"pinned":false,"type":%s%s}';

        $limit = 5;

        $userTemplates = [];
        $usersExpected = [];

        $i = 0;
        foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
            $userTemplates[] = $userTemplate;
            $usersExpected[] = $userExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $attachmentTemplates = [];
        $attachmentsExpected = [];

        $i = 0;
        foreach (AttachmentTest::provider_deserialization() as [$attachmentTemplate, $attachmentExpected]) {
            $attachmentTemplates[] = $attachmentTemplate;
            $attachmentsExpected[] = $attachmentExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $embedTemplates = [];
        $embedsExpected = [];

        $i = 0;
        foreach (EmbedTest::provider_deserialization() as [$embedTemplate, $embedExpected]) {
            $embedTemplates[] = $embedTemplate;
            $embedsExpected[] = $embedExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $channelMentionTemplates = [];
        $channelMentionsExpected = [];

        $i = 0;
        foreach (ChannelMentionTest::provider_deserialization() as [$channelMentionTemplate, $channelMentionExpected]) {
            $channelMentionTemplates[] = $channelMentionTemplate;
            $channelMentionsExpected[] = $channelMentionExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $reactionTemplates = [];
        $reactionsExpected = [];

        $i = 0;
        foreach (ReactionTest::provider_deserialization() as [$reactionTemplate, $reactionExpected]) {
            $reactionTemplates[] = $reactionTemplate;
            $reactionsExpected[] = $reactionExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $actTemplates = [];
        $actsExpected = [];

        $i = 0;
        foreach (MessageActivityTest::provider_deserialization() as [$actTemplate, $actExpected]) {
            $actTemplates[] = $actTemplate;
            $actsExpected[] = $actExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $appTemplates = [];
        $appsExpected = [];

        $i = 0;
        foreach (ApplicationTest::provider_deserialization() as [$appTemplate, $appExpected]) {
            $appTemplates[] = $appTemplate;
            $appsExpected[] = $appExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $refTemplates = [];
        $refsExpected = [];

        $i = 0;
        foreach (MessageReferenceTest::provider_deserialization() as [$refTemplate, $refExpected]) {
            $refTemplates[] = $refTemplate;
            $refsExpected[] = $refExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $snapTemplates = [];
        $snapsExpected = [];

        $i = 0;
        foreach (MessageSnapshotTest::provider_deserialization() as [$snapTemplate, $snapExpected]) {
            $snapTemplates[] = $snapTemplate;
            $snapsExpected[] = $snapExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $metaTemplates = [];
        $metasExpected = [];

        $i = 0;
        foreach (MessageInteractionMetadataTest::provider_deserialization() as [$metaTemplate, $metaExpected]) {
            $metaTemplates[] = $metaTemplate;
            $metasExpected[] = $metaExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $intTemplates = [];
        $intsExpected = [];

        $i = 0;
        foreach (MessageInteractionTest::provider_deserialization() as [$intTemplate, $intExpected]) {
            $intTemplates[] = $intTemplate;
            $intsExpected[] = $intExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $channelTemplates = [];
        $channelsExpected = [];

        $i = 0;
        foreach (ChannelTest::provider_deserialization() as [$channelTemplate, $channelExpected]) {
            $channelTemplates[] = $channelTemplate;
            $channelsExpected[] = $channelExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $componentTemplates = [];
        $componentsExpected = [];

        $i = 0;
        foreach (ActionRowComponentTest::provider_deserialization() as [$componentTemplate, $componentExpected]) {
            $componentTemplates[] = $componentTemplate;
            $componentsExpected[] = $componentExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $itemTemplates = [];
        $itemsExpected = [];

        $i = 0;
        foreach (StickerItemTest::provider_deserialization() as [$itemTemplate, $itemExpected]) {
            $itemTemplates[] = $itemTemplate;
            $itemsExpected[] = $itemExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $stickerTemplates = [];
        $stickersExpected = [];

        $i = 0;
        foreach (StickerTest::provider_deserialization() as [$stickerTemplate, $stickerExpected]) {
            $stickerTemplates[] = $stickerTemplate;
            $stickersExpected[] = $stickerExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $subTemplates = [];
        $subsExpected = [];

        $i = 0;
        foreach (RoleSubscriptionDataTest::provider_deserialization() as [$subTemplate, $subExpected]) {
            $subTemplates[] = $subTemplate;
            $subsExpected[] = $subExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $resolvedTemplates = [];
        $resolvedsExpected = [];

        $i = 0;
        foreach (ResolvedDataTest::provider_deserialization() as [$resolvedTemplate, $resolvedExpected]) {
            $resolvedTemplates[] = $resolvedTemplate;
            $resolvedsExpected[] = $resolvedExpected;

            if ($i++ >= $limit) {
                break;
            }
        }

        $pollTemplates = [];
        $pollsExpected = [];

        foreach (PollTest::provider_deserialization() as [$pollTemplate, $pollExpected]) {
            $pollTemplates[] = $pollTemplate;
            $pollsExpected[] = $pollExpected;
        }

        $callTemplates = [];
        $callsExpected = [];

        foreach (MessageCallTest::provider_deserialization() as [$callTemplate, $callExpected]) {
            $callTemplates[] = $callTemplate;
            $callsExpected[] = $callExpected;
        }

        $data = [];

        foreach (MessageType::cases() as $type) {
            $authorIx = array_rand($usersExpected);
            $actIx = array_rand($actsExpected);
            $appIx = array_rand($appsExpected);
            $refIx = array_rand($refsExpected);
            $metaIx = array_rand($metasExpected);
            $intIx = array_rand($intsExpected);
            $channelIx = array_rand($channelsExpected);
            $subIx = array_rand($subsExpected);
            $resolvedIx = array_rand($resolvedsExpected);
            $pollIx = array_rand($pollsExpected);
            $callIx = array_rand($callsExpected);

            $data[] = [
                sprintf(
                    $subjectTemplate,
                    $userTemplates[$authorIx],
                    implode(',', $userTemplates),
                    implode(',', $attachmentTemplates),
                    implode(',', $embedTemplates),
                    $type->value,
                    ',"mention_channels":[' . implode(',', $channelMentionTemplates) . '],"reactions":[' . implode(',', $reactionTemplates) . '],"nonce":"test-nonce","webhook_id":"test-webhook-id","activity":' . $actTemplates[$actIx] . ',"application":' . $appTemplates[$appIx] . ',"application_id":"test-application-id","flags":2,"message_reference":' . $refTemplates[$refIx] . ',"message_snapshots":[' . implode(',', $snapTemplates) . '],"interaction_metadata":' . $metaTemplates[$metaIx] . ',"interaction":' . $intTemplates[$intIx] . ',"thread":' . $channelTemplates[$channelIx] . ',"components":[' . implode(',', $componentTemplates) . '],"sticker_items":[' . implode(',', $itemTemplates) . '],"stickers":[' . implode(',', $stickerTemplates) . '],"position":3,"role_subscription_data":' . $subTemplates[$subIx] . ',"resolved":' . $resolvedTemplates[$resolvedIx] . ',"poll":' . $pollTemplates[$pollIx] . ',"call":' . $callTemplates[$callIx]
                ),
                new Message(
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
                    type: $type,
                    mention_channels: $channelMentionsExpected,
                    reactions: $reactionsExpected,
                    nonce: 'test-nonce',
                    webhook_id: 'test-webhook-id',
                    activity: $actsExpected[$actIx],
                    application: $appsExpected[$appIx],
                    application_id: 'test-application-id',
                    flags: 2,
                    message_reference: $refsExpected[$refIx],
                    message_snapshots: $snapsExpected,
                    interaction_metadata: $metasExpected[$metaIx],
                    interaction: $intsExpected[$intIx],
                    thread: $channelsExpected[$channelIx],
                    components: $componentsExpected,
                    sticker_items: $itemsExpected,
                    stickers: $stickersExpected,
                    position: 3,
                    role_subscription_data: $subsExpected[$subIx],
                    resolved: $resolvedsExpected[$resolvedIx],
                    poll: $pollsExpected[$pollIx],
                    call: $callsExpected[$callIx]
                )
            ];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param Message $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Message $expected): void
    {
        self::testDeserialization($subject, $expected, Message::class);
    }
}

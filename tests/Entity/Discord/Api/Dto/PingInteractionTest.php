<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\PingInteraction;
use App\Entity\Discord\Api\Enumeration\InteractionContextType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\PingInteraction
 * @covers \App\Entity\Discord\Api\Dto\AbstractInteraction
 */
final class PingInteractionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param PingInteraction $expected
     * @param PingInteraction $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->application_id, $actual->application_id);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->data, $actual->data);
        self::assertSame($expected->token, $actual->token);
        self::assertSame($expected->app_permissions, $actual->app_permissions);

        self::assertSame(count($expected->entitlements), count($actual->entitlements));

        for ($i = 0; $i < count($expected->entitlements); ++$i) {
            EntitlementTest::assertDeepSame($expected->entitlements[$i], $actual->entitlements[$i]);
        }

        self::assertSame(count($expected->authorizing_integration_owners), count($actual->authorizing_integration_owners));

        for ($i = 0; $i < count($expected->authorizing_integration_owners); ++$i) {
            self::assertSame($expected->authorizing_integration_owners[$i], $actual->authorizing_integration_owners[$i]);
        }

        if (isset($expected->guild)) {
            GuildTest::assertDeepSame($expected->guild, $actual->guild);
        } else {
            self::assertNull($actual->guild);
        }

        self::assertSame($expected->guild_id, $actual->guild_id);

        if (isset($expected->channel)) {
            ChannelTest::assertDeepSame($expected->channel, $actual->channel);
        } else {
            self::assertNull($actual->channel);
        }

        self::assertSame($expected->channel_id, $actual->channel_id);

        if (isset($expected->member)) {
            GuildMemberTest::assertDeepSame($expected->member, $actual->member);
        } else {
            self::assertNull($actual->member);
        }

        if (isset($expected->user)) {
            UserTest::assertDeepSame($expected->user, $actual->user);
        } else {
            self::assertNull($actual->user);
        }

        if (isset($expected->message)) {
            MessageTest::assertDeepSame($expected->message, $actual->message);
        } else {
            self::assertNull($actual->message);
        }

        self::assertSame($expected->locale, $actual->locale);
        self::assertSame($expected->guild_locale, $actual->guild_locale);
        self::assertSame($expected->context, $actual->context);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","application_id":"test-application-id","type":1,"token":"test-token","app_permissions":"test-app-permissions","entitlements":[%s],"authorizing_integration_owners":[%s]%s}';

        $entTemplates = [];
        $entsExpected = [];

        foreach (EntitlementTest::provider_deserialization() as [$entTemplate, $entExpected]) {
            $entTemplates[] = $entTemplate;
            $entsExpected[] = $entExpected;
        }

        $guildTemplates = [];
        $guildsExpected = [];

        foreach (GuildTest::provider_deserialization() as [$guildTemplate, $guildExpected]) {
            $guildTemplates[] = $guildTemplate;
            $guildsExpected[] = $guildExpected;
        }

        $channelTemplates = [];
        $channelsExpected = [];

        foreach (ChannelTest::provider_deserialization() as [$channelTemplate, $channelExpected]) {
            $channelTemplates[] = $channelTemplate;
            $channelsExpected[] = $channelExpected;
        }

        $memberTemplates = [];
        $membersExpected = [];

        foreach (GuildMemberTest::provider_deserialization() as [$memberTemplate, $memberExpected]) {
            $memberTemplates[] = $memberTemplate;
            $membersExpected[] = $memberExpected;
        }

        $userTemplates = [];
        $usersExpected = [];

        foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
            $userTemplates[] = $userTemplate;
            $usersExpected[] = $userExpected;
        }

        $messageTemplates = [];
        $messagesExpected = [];
        foreach (MessageTest::provider_deserialization() as [$messageTemplate, $messageExpected]) {
            $messageTemplates[] = $messageTemplate;
            $messagesExpected[] = $messageExpected;
        }

        $data = [];

        foreach (InteractionContextType::cases() as $context) {
            $guildIx = array_rand($guildsExpected);
            $channelIx = array_rand($channelsExpected);
            $memberIx = array_rand($membersExpected);
            $userIx = array_rand($usersExpected);
            $messageIx = array_rand($messagesExpected);

            $data[] = [
                sprintf($subjectTemplate, implode(',', $entTemplates), '"test-1","test-2"', ',"guild":' . $guildTemplates[$guildIx] . ',"guild_id":"test-guild-id","channel":' . $channelTemplates[$channelIx] . ',"channel_id":"test-channel-id","member":' . $memberTemplates[$memberIx] . ',"user":' . $userTemplates[$userIx] . ',"message":' . $messageTemplates[$messageIx] . ',"locale":"test-locale","guild_locale":"test-guild-locale","context":' . $context->value),
                new PingInteraction(
                    id: 'test-id',
                    application_id: 'test-application-id',
                    token: 'test-token',
                    app_permissions: 'test-app-permissions',
                    entitlements: $entsExpected,
                    authorizing_integration_owners: ['test-1', 'test-2'],
                    guild: $guildsExpected[$guildIx],
                    guild_id: 'test-guild-id',
                    channel: $channelsExpected[$channelIx],
                    channel_id: 'test-channel-id',
                    member: $membersExpected[$memberIx],
                    user: $usersExpected[$userIx],
                    message: $messagesExpected[$messageIx],
                    locale: 'test-locale',
                    guild_locale: 'test-guild-locale',
                    context: $context
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, '', '', ''),
                new PingInteraction(
                    id: 'test-id',
                    application_id: 'test-application-id',
                    token: 'test-token',
                    app_permissions: 'test-app-permissions',
                    entitlements: [],
                    authorizing_integration_owners: []
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param PingInteraction $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, PingInteraction $expected): void
    {
        self::testDeserialization($subject, $expected, PingInteraction::class);
    }
}

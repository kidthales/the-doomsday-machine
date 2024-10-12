<?php

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\WelcomeScreenChannel;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

class WelcomeScreenChannelTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param WelcomeScreenChannel $expected
     * @param WelcomeScreenChannel $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->channel_id, $actual->channel_id);
        self::assertSame($expected->description, $actual->description);
        self::assertSame($expected->emoji_id, $actual->emoji_id);
        self::assertSame($expected->emoji_name, $actual->emoji_name);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"channel_id":"test-channel-id","description":"test-description","emoji_id":%s,"emoji_name":%s}';

        return [
            [
                sprintf($subjectTemplate, 'null', 'null'),
                new WelcomeScreenChannel(
                    channel_id: 'test-channel-id',
                    description: 'test-description',
                    emoji_id: null,
                    emoji_name: null
                ),
            ],
            [
                sprintf($subjectTemplate, '"test-emoji-id"', 'null'),
                new WelcomeScreenChannel(
                    channel_id: 'test-channel-id',
                    description: 'test-description',
                    emoji_id: 'test-emoji-id',
                    emoji_name: null
                ),
            ],
            [
                sprintf($subjectTemplate, 'null', '"test-emoji-name"'),
                new WelcomeScreenChannel(
                    channel_id: 'test-channel-id',
                    description: 'test-description',
                    emoji_id: null,
                    emoji_name: 'test-emoji-name'
                ),
            ],
            [
                sprintf($subjectTemplate, '"test-emoji-id"', '"test-emoji-name"'),
                new WelcomeScreenChannel(
                    channel_id: 'test-channel-id',
                    description: 'test-description',
                    emoji_id: 'test-emoji-id',
                    emoji_name: 'test-emoji-name'
                ),
            ]
        ];
    }

    /**
     * @param string $subject
     * @param WelcomeScreenChannel $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, WelcomeScreenChannel $expected): void
    {
        self::testDeserialization($subject, $expected, WelcomeScreenChannel::class);
    }
}

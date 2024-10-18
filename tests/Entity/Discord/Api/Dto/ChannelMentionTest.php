<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ChannelMention;
use App\Entity\Discord\Api\Enumeration\ChannelType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ChannelMention
 */
class ChannelMentionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ChannelMention $expected
     * @param ChannelMention $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->guild_id, $actual->guild_id);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->name, $actual->name);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","guild_id":"test-guild-id","type":%s,"name":"test-name"}';

        $data = [];

        foreach (ChannelType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, $type->value),
                new ChannelMention(id: 'test-id', guild_id: 'test-guild-id', type: $type, name: 'test-name')
            ];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param ChannelMention $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ChannelMention $expected): void
    {
        self::testDeserialization($subject, $expected, ChannelMention::class);
    }
}

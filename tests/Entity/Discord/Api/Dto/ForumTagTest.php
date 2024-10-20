<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ForumTag;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ForumTag
 */
final class ForumTagTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ForumTag $expected
     * @param ForumTag $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->moderated, $actual->moderated);
        self::assertSame($expected->emoji_id, $actual->emoji_id);
        self::assertSame($expected->emoji_name, $actual->emoji_name);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","name":"test-name","moderated":true,"emoji_id":%s,"emoji_name":%s}';

        $baseExpected = new ForumTag(
            id: 'test-id',
            name: 'test-name',
            moderated: true,
            emoji_id: null,
            emoji_name: null
        );

        $withStringEmojiId = clone $baseExpected;
        $withStringEmojiId->emoji_id = 'test-emoji-id';

        $withStringEmojiName = clone $baseExpected;
        $withStringEmojiName->emoji_name = 'test-emoji-name';

        return [
            [sprintf($subjectTemplate, 'null', 'null'), $baseExpected],
            [sprintf($subjectTemplate, '"test-emoji-id"', 'null'), $withStringEmojiId],
            [sprintf($subjectTemplate, 'null', '"test-emoji-name"'), $withStringEmojiName]
        ];
    }

    /**
     * @param string $subject
     * @param ForumTag $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ForumTag $expected): void
    {
        self::testDeserialization($subject, $expected, ForumTag::class);
    }
}

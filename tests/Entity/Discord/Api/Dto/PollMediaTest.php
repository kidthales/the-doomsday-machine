<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\PollMedia;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\PollMedia
 */
final class PollMediaTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param PollMedia $expected
     * @param PollMedia $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->text, $actual->text);

        if (isset($expected->emoji)) {
            EmojiTest::assertDeepSame($expected->emoji, $actual->emoji);
        } else {
            self::assertNull($actual->emoji);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{%s}';

        $data = [];

        foreach (EmojiTest::provider_deserialization() as [$emojiTemplate, $emojiExpected]) {
            $data[] = [
                sprintf($subjectTemplate, '"text":"test-text","emoji":' . $emojiTemplate),
                new PollMedia(text: 'test-text', emoji: $emojiExpected)
            ];
        }

        return [
            [sprintf($subjectTemplate, ''), new PollMedia()],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param PollMedia $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, PollMedia $expected): void
    {
        self::testDeserialization($subject, $expected, PollMedia::class);
    }
}

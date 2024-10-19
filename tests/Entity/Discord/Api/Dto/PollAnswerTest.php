<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\PollAnswer;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\PollAnswer
 */
final class PollAnswerTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param PollAnswer $expected
     * @param PollAnswer $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->answer_id, $actual->answer_id);

        PollMediaTest::assertDeepSame($expected->poll_media, $actual->poll_media);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"answer_id":6,"poll_media":%s}';

        $data = [];

        foreach (PollMediaTest::provider_deserialization() as [$mediaTemplate, $mediaExpected]) {
            $data[] = [
                sprintf($subjectTemplate, $mediaTemplate),
                new PollAnswer(answer_id: 6, poll_media: $mediaExpected)
            ];
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param PollAnswer $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, PollAnswer $expected): void
    {
        self::testDeserialization($subject, $expected, PollAnswer::class);
    }
}

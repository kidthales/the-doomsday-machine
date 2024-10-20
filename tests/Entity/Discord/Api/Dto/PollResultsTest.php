<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\PollResults;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\PollResults
 */
final class PollResultsTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param PollResults $expected
     * @param PollResults $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->is_finalized, $actual->is_finalized);

        self::assertSame(count($expected->answer_counts), count($actual->answer_counts));

        for ($i = 0; $i < count($expected->answer_counts); ++$i) {
            PollAnswerCountTest::assertDeepSame($expected->answer_counts[$i], $actual->answer_counts[$i]);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"is_finalized":false,"answer_counts":[%s]}';

        $answerTemplates = [];
        $answersExpected = [];

        for ($i = 0; $i < 5; ++$i) {
            [$answerTemplate, $answerExpected] = PollAnswerCountTest::provider_deserialization()[0];
            $answerTemplates[] = $answerTemplate;
            $answersExpected[] = $answerExpected;
        }

        return [
            [sprintf($subjectTemplate, ''), new PollResults(is_finalized: false, answer_counts: [])],
            [
                sprintf($subjectTemplate, implode(',', $answerTemplates)),
                new PollResults(is_finalized: false, answer_counts: $answersExpected)
            ]
        ];
    }

    /**
     * @param string $subject
     * @param PollResults $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, PollResults $expected): void
    {
        self::testDeserialization($subject, $expected, PollResults::class);
    }
}

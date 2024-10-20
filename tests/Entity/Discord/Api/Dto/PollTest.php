<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Poll;
use App\Entity\Discord\Api\Dto\PollMedia;
use App\Entity\Discord\Api\Enumeration\LayoutType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Poll
 */
final class PollTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Poll $expected
     * @param Poll $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        PollMediaTest::assertDeepSame($expected->question, $actual->question);

        self::assertSame(count($expected->answers), count($actual->answers));

        for ($i = 0; $i < count($expected->answers); ++$i) {
            PollAnswerTest::assertDeepSame($expected->answers[$i], $actual->answers[$i]);
        }

        self::assertSame($expected->expiry, $actual->expiry);
        self::assertSame($expected->allow_multiselect, $actual->allow_multiselect);
        self::assertSame($expected->layout_type, $actual->layout_type);

        if (isset($expected->results)) {
            PollResultsTest::assertDeepSame($expected->results, $actual->results);
        } else {
            self::assertNull($actual->results);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"question":%s,"answers":[%s],"expiry":%s,"allow_multiselect":false,"layout_type":1%s}';

        $answerTemplates = [];
        $answersExpected = [];

        foreach (PollAnswerTest::provider_deserialization() as [$answerTemplate, $answerExpected]) {
            $answerTemplates[] = $answerTemplate;
            $answersExpected[] = $answerExpected;
        }

        $data = [];

        foreach (PollMediaTest::provider_deserialization() as [$mediaTemplate, $mediaExpected]) {
            foreach (PollResultsTest::provider_deserialization() as [$resultsTemplate, $resultsExpected]) {
                $data[] = [
                    sprintf($subjectTemplate, $mediaTemplate, implode(',', $answerTemplates), '"test-expiry"', ',"results":' . $resultsTemplate),
                    new Poll(
                        question: $mediaExpected,
                        answers: $answersExpected,
                        expiry: 'test-expiry',
                        allow_multiselect: false,
                        layout_type: LayoutType::DEFAULT,
                        results: $resultsExpected
                    )
                ];
            }
        }

        return [
            [
                sprintf($subjectTemplate, '{}', '', 'null', ''),
                new Poll(
                    question: new PollMedia(),
                    answers: [],
                    expiry: null,
                    allow_multiselect: false,
                    layout_type: LayoutType::DEFAULT
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param Poll $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Poll $expected): void
    {
        self::testDeserialization($subject, $expected, Poll::class);
    }
}

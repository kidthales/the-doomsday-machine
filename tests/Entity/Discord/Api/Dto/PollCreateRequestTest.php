<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\PollCreateRequest;
use App\Entity\Discord\Api\Dto\PollMedia;
use App\Entity\Discord\Api\Enumeration\LayoutType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\PollCreateRequest
 */
final class PollCreateRequestTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param PollCreateRequest $expected
     * @param PollCreateRequest $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        PollMediaTest::assertDeepSame($expected->question, $actual->question);

        self::assertSame(count($expected->answers), count($actual->answers));

        for ($i = 0; $i < count($expected->answers); ++$i) {
            PollAnswerTest::assertDeepSame($expected->answers[$i], $actual->answers[$i]);
        }

        self::assertSame($expected->duration, $actual->duration);
        self::assertSame($expected->allow_multiselect, $actual->allow_multiselect);
        self::assertSame($expected->layout_type, $actual->layout_type);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"question":%s,"answers":[%s]%s}';

        $answerTemplates = [];
        $answersExpected = [];

        foreach (PollAnswerTest::provider_deserialization() as [$answerTemplate, $answerExpected]) {
            $answerTemplates[] = $answerTemplate;
            $answersExpected[] = $answerExpected;
        }

        $data = [];

        foreach (PollMediaTest::provider_deserialization() as [$mediaTemplate, $mediaExpected]) {
            $data[] = [
                sprintf($subjectTemplate, $mediaTemplate, implode(',', $answerTemplates), ',"duration":2,"allow_multiselect":true,"layout_type":1'),
                new PollCreateRequest(
                    question: $mediaExpected,
                    answers: $answersExpected,
                    duration: 2,
                    allow_multiselect: true,
                    layout_type: LayoutType::DEFAULT
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, '{}', '', ''),
                new PollCreateRequest(question: new PollMedia(), answers: [])
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param PollCreateRequest $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, PollCreateRequest $expected): void
    {
        self::testDeserialization($subject, $expected, PollCreateRequest::class);
    }

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $data = [];

        foreach (self::provider_deserialization() as [$template, $expected]) {
            $data[] = [$expected, $template];
        }

        return $data;
    }

    /**
     * @param PollCreateRequest $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(PollCreateRequest $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}

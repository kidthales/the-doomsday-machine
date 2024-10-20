<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\MessageCall;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageCall
 */
final class MessageCallTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageCall $expected
     * @param MessageCall $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame(count($expected->participants), count($actual->participants));

        for ($i = 0; $i < count($expected->participants); ++$i) {
            self::assertSame($expected->participants[$i], $actual->participants[$i]);
        }

        self::assertSame($expected->ended_timestamp, $actual->ended_timestamp);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"participants":[%s]%s}';

        return [
            [sprintf($subjectTemplate, '', ''), new MessageCall(participants: [])],
            [
                sprintf($subjectTemplate, '"test-1","test-2"', ',"ended_timestamp":"test-timestamp"'),
                new MessageCall(participants: ['test-1', 'test-2'], ended_timestamp: 'test-timestamp')
            ],
        ];
    }

    /**
     * @param string $subject
     * @param MessageCall $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageCall $expected): void
    {
        self::testDeserialization($subject, $expected, MessageCall::class);
    }
}

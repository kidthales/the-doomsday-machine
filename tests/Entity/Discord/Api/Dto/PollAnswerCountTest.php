<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\PollAnswerCount;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\PollAnswerCount
 */
final class PollAnswerCountTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param PollAnswerCount $expected
     * @param PollAnswerCount $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->count, $actual->count);
        self::assertSame($expected->me_voted, $actual->me_voted);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        return [['{"id":7,"count":4,"me_voted":true}', new PollAnswerCount(id: 7, count: 4, me_voted: true)]];
    }

    /**
     * @param string $subject
     * @param PollAnswerCount $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, PollAnswerCount $expected): void
    {
        self::testDeserialization($subject, $expected, PollAnswerCount::class);
    }
}

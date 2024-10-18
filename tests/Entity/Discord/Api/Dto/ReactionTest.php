<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Reaction;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Reaction
 */
final class ReactionTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Reaction $expected
     * @param Reaction $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->count, $actual->count);

        ReactionCountDetailsTest::assertDeepSame($expected->count_details, $actual->count_details);

        self::assertSame($expected->me, $actual->me);
        self::assertSame($expected->me_burst, $actual->me_burst);

        EmojiTest::assertDeepSame($expected->emoji, $actual->emoji);

        self::assertSame(count($expected->burst_colors), count($actual->burst_colors));

        for ($i = 0; $i < count($expected->burst_colors); ++$i) {
            self::assertSame($expected->burst_colors[$i], $actual->burst_colors[$i]);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"count":6,"count_details":%s,"me":true,"me_burst":false,"emoji":%s,"burst_colors":[%s]}';

        $burstColors = ['#000000', '#666666', '#dddddd'];

        $data = [];

        foreach (ReactionCountDetailsTest::provider_deserialization() as [$detailsTemplate, $detailsExpected]) {
            foreach (EmojiTest::provider_deserialization() as [$emojiTemplate, $emojiExpected]) {
                $data[] = [
                    sprintf($subjectTemplate, $detailsTemplate, $emojiTemplate, '"' . implode('","', $burstColors) . '"'),
                    new Reaction(
                        count: 6,
                        count_details: $detailsExpected,
                        me: true,
                        me_burst: false,
                        emoji: $emojiExpected,
                        burst_colors: $burstColors
                    )
                ];
            }
        }

        return $data;
    }

    /**
     * @param string $subject
     * @param Reaction $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Reaction $expected): void
    {
        self::testDeserialization($subject, $expected, Reaction::class);
    }
}

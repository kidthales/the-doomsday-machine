<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ReactionCountDetails;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ReactionCountDetails
 */
final class ReactionCountDetailsTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ReactionCountDetails $expected
     * @param ReactionCountDetails $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->burst, $actual->burst);
        self::assertSame($expected->normal, $actual->normal);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        return [['{"burst":14,"normal":66}', new ReactionCountDetails(burst: 14, normal: 66)]];
    }

    /**
     * @param string $subject
     * @param ReactionCountDetails $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ReactionCountDetails $expected): void
    {
        self::testDeserialization($subject, $expected, ReactionCountDetails::class);
    }
}

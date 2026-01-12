<?php

declare(strict_types=1);

namespace App\Tests\FootyStats;

use App\FootyStats\Target;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Target::class)]
final class TargetTest extends TestCase
{
    public static function provide_test_toString(): array
    {
        return [
            'empty' => [
                new Target(),
                ''
            ],
            'nation' => [
                new Target('England'),
                'England'
            ],
            'competition' => [
                new Target(competition: 'Championship'),
                'Championship'
            ],
            'season' => [
                new Target(season: '2025/26'),
                '2025/26'
            ],
            'nation, competition' => [
                new Target('England', 'Championship'),
                'England Championship'
            ],
            'nation, season' => [
                new Target('England', season: '2025/26'),
                'England 2025/26'
            ],
            'competition, season' => [
                new Target(competition: 'Premier League', season: '2025/26'),
                'Premier League 2025/26'
            ],
            'nation, competition, season' => [
                new Target('England', 'Championship', '2025/26'),
                'England Championship 2025/26'
            ]
        ];
    }

    #[DataProvider('provide_test_toString')]
    public function test_toString(Target $subject, string $expected): void
    {
        $actual = (string)$subject;
        self::assertSame($expected, $actual);
    }

    public static function provide_test_snake(): array
    {
        return [
            'empty' => [
                new Target(),
                ''
            ],
            'nation' => [
                new Target('England'),
                'england'
            ],
            'competition' => [
                new Target(competition: 'Championship'),
                'championship'
            ],
            'season' => [
                new Target(season: '2025/26'),
                '202526'
            ],
            'nation, competition' => [
                new Target('England', 'Championship'),
                'england_championship'
            ],
            'nation, season' => [
                new Target('England', season: '2025/26'),
                'england_202526'
            ],
            'competition, season' => [
                new Target(competition: 'Premier League', season: '2025/26'),
                'premier_league_202526'
            ],
            'nation, competition, season' => [
                new Target('England', 'Championship', '2025/26'),
                'england_championship_202526'
            ]
        ];
    }

    #[DataProvider('provide_test_snake')]
    public function test_snake(Target $subject, string $expected): void
    {
        $actual = $subject->snake();
        self::assertSame($expected, $actual);
    }
}

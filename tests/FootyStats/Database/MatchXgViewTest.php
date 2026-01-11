<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database;

use App\FootyStats\Database\MatchXgView;
use App\FootyStats\Target;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MatchXgView::class)]
#[UsesClass(Target::class)]
final class MatchXgViewTest extends TestCase
{
    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'england_championship_202526_match_xg'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'england_premier_league_201213_match_xg'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(Target $subject, string $expected): void
    {
        $actual = MatchXgView::getName($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'DROP VIEW england_championship_202526_match_xg;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP VIEW england_premier_league_201213_match_xg;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = MatchXgView::getDropSql($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                [
                    'CREATE VIEW england_championship_202526_match_xg AS',
                    'FROM england_championship_202526_home_team_standing',
                    'FROM england_championship_202526_away_team_standing',
                    'FROM england_championship_202526_match',
                    'INNER JOIN england_championship_202526_team_strength'
                ]
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                [
                    'CREATE VIEW england_premier_league_201213_match_xg AS',
                    'FROM england_premier_league_201213_home_team_standing',
                    'FROM england_premier_league_201213_away_team_standing',
                    'FROM england_premier_league_201213_match',
                    'INNER JOIN england_premier_league_201213_team_strength'
                ]
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(Target $subject, array $expected): void
    {
        $actual = MatchXgView::getCreateSql($subject);

        foreach ($expected as $exp) {
            self::assertStringContainsString($exp, $actual);
        }
    }
}

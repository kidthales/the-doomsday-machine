<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database;

use App\FootyStats\Database\TeamStandingView;
use App\FootyStats\Target;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamStandingView::class)]
#[UsesClass(Target::class)]
final class TeamStandingViewTest extends TestCase
{
    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'england_championship_202526_team_standing'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'england_premier_league_201213_team_standing'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(Target $subject, string $expected): void
    {
        $actual = TeamStandingView::getName($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'DROP VIEW england_championship_202526_team_standing;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP VIEW england_premier_league_201213_team_standing;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = TeamStandingView::getDropSql($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                [
                    'CREATE VIEW england_championship_202526_team_standing AS',
                    'FROM england_championship_202526_match'
                ]
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                [
                    'CREATE VIEW england_premier_league_201213_team_standing AS',
                    'FROM england_premier_league_201213_match'
                ]
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(Target $subject, array $expected): void
    {
        $actual = TeamStandingView::getCreateSql($subject);

        foreach ($expected as $exp) {
            self::assertStringContainsString($exp, $actual);
        }
    }
}

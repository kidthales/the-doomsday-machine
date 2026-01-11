<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database;

use App\FootyStats\Database\TeamStandingView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TeamStandingViewTest extends TestCase
{
    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                ['England', 'Championship', '2025/26'],
                'england_championship_202526_team_standing'
            ],
            'England Premier League 2012/13' => [
                ['England', 'Premier League', '2012/13'],
                'england_premier_league_201213_team_standing'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(array $subject, string $expected): void
    {
        $actual = TeamStandingView::getName(...$subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                ['England', 'Championship', '2025/26'],
                'DROP VIEW england_championship_202526_team_standing;'
            ],
            'England Premier League 2012/13' => [
                ['England', 'Premier League', '2012/13'],
                'DROP VIEW england_premier_league_201213_team_standing;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(array $subject, string $expected): void
    {
        $actual = TeamStandingView::getDropSql(...$subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                ['England', 'Championship', '2025/26'],
                'CREATE VIEW england_championship_202526_team_standing AS'
            ],
            'England Premier League 2012/13' => [
                ['England', 'Premier League', '2012/13'],
                'CREATE VIEW england_premier_league_201213_team_standing AS'
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(array $subject, string $expected): void
    {
        $actual = TeamStandingView::getCreateSql(...$subject);
        self::assertStringContainsString($expected, $actual);
    }
}

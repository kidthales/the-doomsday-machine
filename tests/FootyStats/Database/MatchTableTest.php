<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database;

use App\FootyStats\Database\MatchTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MatchTableTest extends TestCase
{
    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                ['England', 'Championship', '2025/26'],
                'england_championship_202526_match'
            ],
            'England Premier League 2012/13' => [
                ['England', 'Premier League', '2012/13'],
                'england_premier_league_201213_match'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(array $subject, string $expected): void
    {
        $actual = MatchTable::getName(...$subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                ['England', 'Championship', '2025/26'],
                'DROP TABLE england_championship_202526_match;'
            ],
            'England Premier League 2012/13' => [
                ['England', 'Premier League', '2012/13'],
                'DROP TABLE england_premier_league_201213_match;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(array $subject, string $expected): void
    {
        $actual = MatchTable::getDropSql(...$subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                ['England', 'Championship', '2025/26'],
                'CREATE TABLE england_championship_202526_match ('
            ],
            'England Premier League 2025/26' => [
                ['England', 'Premier League', '2012/13'],
                'CREATE TABLE england_premier_league_201213_match ('
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(array $subject, string $expected): void
    {
        $actual = MatchTable::getCreateSql(...$subject);
        self::assertStringContainsString($expected, $actual);
    }
}

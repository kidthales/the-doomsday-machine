<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database;

use App\FootyStats\Database\MatchTable;
use App\FootyStats\Target;
use App\Tests\FootyStats\Database\Trait\MatchTableSetUpTearDownTrait;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(MatchTable::class)]
#[UsesClass(Target::class)]
final class MatchTableTest extends AbstractDatabaseTestCase
{
    use MatchTableSetUpTearDownTrait;

    /**
     * @return void
     * @throws DBALException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMatchTable();
    }

    /**
     * @return void
     * @throws DBALException
     */
    public function tearDown(): void
    {
        $this->tearDownMatchTable();
        parent::tearDown();
    }

    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'england_championship_202526_match'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'england_premier_league_201213_match'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(Target $subject, string $expected): void
    {
        $actual = MatchTable::getName($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'DROP TABLE england_championship_202526_match;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP TABLE england_premier_league_201213_match;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = MatchTable::getDropSql($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'CREATE TABLE england_championship_202526_match ('
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'CREATE TABLE england_premier_league_201213_match ('
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(Target $subject, string $expected): void
    {
        $actual = MatchTable::getCreateSql($subject);
        self::assertStringContainsString($expected, $actual);
    }

    public static function provide_test_exists(): array
    {
        return [
            'true' => [new Target('Test', 'Test', 'Test'), true],
            'false' => [new Target('Test', 'Test', 'Not Found'), false],
        ];
    }

    /**
     * @param Target $subject
     * @param bool $expected
     * @return void
     * @throws DBALException
     */
    #[DataProvider('provide_test_exists')]
    public function test_exists(Target $subject, bool $expected): void
    {
        /** @var MatchTable $matchTable */
        $matchTable = self::getContainer()->get(MatchTable::class);

        $actual = $matchTable->exists($subject);
        self::assertSame($expected, $actual);
    }
}

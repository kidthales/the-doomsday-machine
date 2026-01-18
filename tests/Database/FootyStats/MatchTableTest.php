<?php

declare(strict_types=1);

namespace App\Tests\Database\FootyStats;

use App\Database\FootyStats\MatchTable;
use App\Entity\FootyStats\Target;
use App\Tests\Database\FootyStats\Trait\MatchTableSetUpTearDownTrait;
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
                'footy_stats_england_championship_202526_match'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'footy_stats_england_premier_league_201213_match'
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
                'DROP TABLE footy_stats_england_championship_202526_match;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP TABLE footy_stats_england_premier_league_201213_match;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = MatchTable::getDropSql($subject);
        self::assertStringContainsString($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'CREATE TABLE footy_stats_england_championship_202526_match ('
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'CREATE TABLE footy_stats_england_premier_league_201213_match ('
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

    /**
     * @return void
     * @throws DBALException
     */
    public function test_CRUD(): void
    {
        /** @var MatchTable $matchTable */
        $matchTable = self::getContainer()->get(MatchTable::class);

        $insertQueryBuilder = $matchTable->createInsertQueryBuilder($this->target);

        $insertQueryBuilder->values([
            'home_team_name' => '?',
            'away_team_name' => '?',
            'home_team_score' => '?',
            'away_team_score' => '?',
            'timestamp' => '?',
            'extra' => '?'
        ]);

        $insertQueryBuilder->setParameters(['Test Team A', 'Test Team B', 2, 1, 100, null])->executeStatement();
        $insertQueryBuilder->setParameters(['Test Team C', 'Test Team D', 3, 3, 100, null])->executeStatement();

        $insertQueryBuilder->setParameters(['Test Team B', 'Test Team C', null, null, 200, null])->executeStatement();
        $insertQueryBuilder->setParameters(['Test Team D', 'Test Team A', 2, 2, 200, null])->executeStatement();

        $insertQueryBuilder->setParameters(['Test Team A', 'Test Team C', 4, 0, 300, null])->executeStatement();
        $insertQueryBuilder->setParameters(['Test Team B', 'Test Team D', 1, 2, 300, null])->executeStatement();

        $selectQueryBuilder = $matchTable->createSelectQueryBuilder($this->target, 't');

        self::assertSame(6, $selectQueryBuilder->select('COUNT(*)')->fetchOne());

        $match = $selectQueryBuilder
            ->select('*')
            ->where('t.home_team_name = :home_team_name')
            ->andWhere('t.away_team_name = :away_team_name')
            ->setParameter('home_team_name', 'Test Team B')
            ->setParameter('away_team_name', 'Test Team C')
            ->fetchAssociative();

        self::assertNull($match['home_team_score']);
        self::assertNull($match['away_team_score']);
        self::assertSame(200, $match['timestamp']);

        $matchTable->createUpdateQueryBuilder($this->target)
            ->set('home_team_score', ':home_team_score')
            ->set('away_team_score', ':away_team_score')
            ->set('timestamp', ':timestamp')
            ->where('home_team_name = :home_team_name')
            ->andWhere('away_team_name = :away_team_name')
            ->setParameter('home_team_score', 1)
            ->setParameter('away_team_score', 0)
            ->setParameter('timestamp', 500)
            ->setParameter('home_team_name', 'Test Team B')
            ->setParameter('away_team_name', 'Test Team C')
            ->executeStatement();

        $match = $selectQueryBuilder->fetchAssociative();

        self::assertSame(1, $match['home_team_score']);
        self::assertSame(0, $match['away_team_score']);
        self::assertSame(500, $match['timestamp']);

        $deleteQueryBuilder = $matchTable->createDeleteQueryBuilder($this->target);

        $deleteQueryBuilder
            ->where('home_team_name = :home_team_name')
            ->setParameter('home_team_name', 'Test Team A')
            ->executeStatement();

        self::assertSame(4, $selectQueryBuilder->resetWhere()->select('COUNT(*)')->fetchOne());

        $deleteQueryBuilder->resetWhere()->executeStatement();

        self::assertSame(0, $selectQueryBuilder->select('COUNT(*)')->fetchOne());
    }
}

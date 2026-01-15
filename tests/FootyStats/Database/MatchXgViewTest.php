<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database;

use App\FootyStats\Database\MatchXgView;
use App\FootyStats\Target;
use App\Tests\FootyStats\Database\Trait\AwayTeamStandingViewSetUpTearDownTrait;
use App\Tests\FootyStats\Database\Trait\HomeTeamStandingViewSetUpTearDownTrait;
use App\Tests\FootyStats\Database\Trait\MatchTableSetUpTearDownTrait;
use App\Tests\FootyStats\Database\Trait\TeamStrengthViewSetUpTearDownTrait;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Throwable;

#[CoversClass(MatchXgView::class)]
#[UsesClass(Target::class)]
final class MatchXgViewTest extends AbstractDatabaseTestCase
{
    use MatchTableSetUpTearDownTrait,
        HomeTeamStandingViewSetUpTearDownTrait,
        AwayTeamStandingViewSetUpTearDownTrait,
        TeamStrengthViewSetUpTearDownTrait;

    /**
     * @return void
     * @throws DBALException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMatchTable();
        $this->setUpHomeTeamStandingView();
        $this->setUpAwayTeamStandingView();
        $this->setUpTeamStrengthView();

        try {
            $this->connection->executeStatement(MatchXgView::getDropSql($this->target));
        } catch (Throwable) {}

        $this->connection->executeStatement(MatchXgView::getCreateSql($this->target));
    }

    /**
     * @return void
     * @throws DBALException
     */
    public function tearDown(): void
    {
        $this->connection->executeStatement(MatchXgView::getDropSql($this->target));

        $this->tearDownTeamStrengthView();
        $this->tearDownAwayTeamStandingView();
        $this->tearDownHomeTeamStandingView();
        $this->tearDownMatchTable();
        parent::tearDown();
    }

    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'footy_stats_england_championship_202526_match_xg'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'footy_stats_england_premier_league_201213_match_xg'
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
                'DROP VIEW footy_stats_england_championship_202526_match_xg;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP VIEW footy_stats_england_premier_league_201213_match_xg;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = MatchXgView::getDropSql($subject);
        self::assertStringContainsString($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                [
                    'CREATE VIEW footy_stats_england_championship_202526_match_xg AS',
                    'FROM footy_stats_england_championship_202526_home_team_standing',
                    'FROM footy_stats_england_championship_202526_away_team_standing',
                    'FROM footy_stats_england_championship_202526_match',
                    'INNER JOIN footy_stats_england_championship_202526_team_strength'
                ]
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                [
                    'CREATE VIEW footy_stats_england_premier_league_201213_match_xg AS',
                    'FROM footy_stats_england_premier_league_201213_home_team_standing',
                    'FROM footy_stats_england_premier_league_201213_away_team_standing',
                    'FROM footy_stats_england_premier_league_201213_match',
                    'INNER JOIN footy_stats_england_premier_league_201213_team_strength'
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
        /** @var MatchXgView $matchXgView */
        $matchXgView = self::getContainer()->get(MatchXgView::class);

        $actual = $matchXgView->exists($subject);
        self::assertSame($expected, $actual);
    }
}

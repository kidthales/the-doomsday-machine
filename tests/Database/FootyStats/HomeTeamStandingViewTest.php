<?php

declare(strict_types=1);

namespace App\Tests\Database\FootyStats;

use App\Database\FootyStats\HomeTeamStandingView;
use App\Entity\FootyStats\Target;
use App\Tests\Database\FootyStats\Trait\HomeTeamStandingViewSetUpTearDownTrait;
use App\Tests\Database\FootyStats\Trait\MatchTableSetUpTearDownTrait;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(HomeTeamStandingView::class)]
#[UsesClass(Target::class)]
final class HomeTeamStandingViewTest extends AbstractDatabaseTestCase
{
    use MatchTableSetUpTearDownTrait, HomeTeamStandingViewSetUpTearDownTrait;

    /**
     * @return void
     * @throws DBALException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMatchTable();
        $this->setUpHomeTeamStandingView();
    }

    /**
     * @return void
     * @throws DBALException
     */
    public function tearDown(): void
    {
        $this->tearDownHomeTeamStandingView();
        $this->tearDownMatchTable();
        parent::tearDown();
    }

    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'footy_stats_england_championship_202526_home_team_standing'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'footy_stats_england_premier_league_201213_home_team_standing'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(Target $subject, string $expected): void
    {
        $actual = HomeTeamStandingView::getName($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'DROP VIEW footy_stats_england_championship_202526_home_team_standing;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP VIEW footy_stats_england_premier_league_201213_home_team_standing;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = HomeTeamStandingView::getDropSql($subject);
        self::assertStringContainsString($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                [
                    'CREATE VIEW footy_stats_england_championship_202526_home_team_standing AS',
                    'FROM footy_stats_england_championship_202526_match'
                ]
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                [
                    'CREATE VIEW footy_stats_england_premier_league_201213_home_team_standing AS',
                    'FROM footy_stats_england_premier_league_201213_match'
                ]
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(Target $subject, array $expected): void
    {
        $actual = HomeTeamStandingView::getCreateSql($subject);

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
        /** @var HomeTeamStandingView $homeTeamStandingView */
        $homeTeamStandingView = self::getContainer()->get(HomeTeamStandingView::class);

        $actual = $homeTeamStandingView->exists($subject);
        self::assertSame($expected, $actual);
    }
}

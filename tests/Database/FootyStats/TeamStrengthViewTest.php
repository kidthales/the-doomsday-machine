<?php

declare(strict_types=1);

namespace App\Tests\Database\FootyStats;

use App\Database\FootyStats\TeamStrengthView;
use App\Entity\FootyStats\Target;
use App\Tests\Database\FootyStats\Trait\MatchTableSetUpTearDownTrait;
use App\Tests\Database\FootyStats\Trait\TeamStandingViewSetUpTearDownTrait;
use App\Tests\Database\FootyStats\Trait\TeamStrengthViewSetUpTearDownTrait;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(TeamStrengthView::class)]
#[UsesClass(Target::class)]
final class TeamStrengthViewTest extends AbstractDatabaseTestCase
{
    use MatchTableSetUpTearDownTrait, TeamStandingViewSetUpTearDownTrait, TeamStrengthViewSetUpTearDownTrait;

    /**
     * @return void
     * @throws DBALException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpMatchTable();
        $this->setUpTeamStandingView();
        $this->setUpTeamStrengthView();
    }

    /**
     * @return void
     * @throws DBALException
     */
    public function tearDown(): void
    {
        $this->tearDownTeamStrengthView();
        $this->tearDownTeamStandingView();
        $this->tearDownMatchTable();
        parent::tearDown();
    }

    public static function provide_test_getName(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'footy_stats_england_championship_202526_team_strength'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'footy_stats_england_premier_league_201213_team_strength'
            ],
        ];
    }

    #[DataProvider('provide_test_getName')]
    public function test_getName(Target $subject, string $expected): void
    {
        $actual = TeamStrengthView::getName($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_getDropSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                'DROP VIEW footy_stats_england_championship_202526_team_strength;'
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                'DROP VIEW footy_stats_england_premier_league_201213_team_strength;'
            ],
        ];
    }

    #[DataProvider('provide_test_getDropSql')]
    public function test_getDropSql(Target $subject, string $expected): void
    {
        $actual = TeamStrengthView::getDropSql($subject);
        self::assertStringContainsString($expected, $actual);
    }

    public static function provide_test_getCreateSql(): array
    {
        return [
            'England Championship 2025/26' => [
                new Target('England', 'Championship', '2025/26'),
                [
                    'CREATE VIEW footy_stats_england_championship_202526_team_strength AS',
                    'FROM footy_stats_england_championship_202526_team_standing'
                ]
            ],
            'England Premier League 2012/13' => [
                new Target('England', 'Premier League', '2012/13'),
                [
                    'CREATE VIEW footy_stats_england_premier_league_201213_team_strength AS',
                    'FROM footy_stats_england_premier_league_201213_team_standing'
                ]
            ],
        ];
    }

    #[DataProvider('provide_test_getCreateSql')]
    public function test_getCreateSql(Target $subject, array $expected): void
    {
        $actual = TeamStrengthView::getCreateSql($subject);

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
        /** @var TeamStrengthView $teamStrengthView */
        $teamStrengthView = self::getContainer()->get(TeamStrengthView::class);

        $actual = $teamStrengthView->exists($subject);
        self::assertSame($expected, $actual);
    }
}

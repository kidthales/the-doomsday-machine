<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(FootballMatch::class)]
final class FootballMatchTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $team = new FootballMatch();
        $this->assertNull($team->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_competition(): void
    {
        $match = new FootballMatch();
        $cmp = new FootballCompetition();
        $this->assertNull($match->getCompetition());
        $this->assertSame($match, $match->setCompetition($cmp));
        $this->assertSame($cmp, $match->getCompetition());
    }

    #[Test]
    public function it_has_getter_and_setter_for_home_team(): void
    {
        $match = new FootballMatch();
        $team = new FootballTeam();
        $this->assertNull($match->getHomeTeam());
        $this->assertSame($match, $match->setHomeTeam($team));
        $this->assertSame($team, $match->getHomeTeam());
    }

    #[Test]
    public function it_has_getter_and_setter_for_away_team(): void
    {
        $match = new FootballMatch();
        $team = new FootballTeam();
        $this->assertNull($match->getAwayTeam());
        $this->assertSame($match, $match->setAwayTeam($team));
        $this->assertSame($team, $match->getAwayTeam());
    }

    #[Test]
    public function it_has_getter_and_setter_for_timestamp(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getTimestamp());
        $this->assertSame($match, $match->setTimestamp(1337));
        $this->assertSame(1337, $match->getTimestamp());
    }

    #[Test]
    public function it_has_getter_and_setter_for_round(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getRound());
        $this->assertSame($match, $match->setRound(1));
        $this->assertSame(1, $match->getRound());
    }

    #[Test]
    public function it_has_getter_and_setter_for_home_team_halftime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getHomeTeamHalftimeScore());
        $this->assertSame($match, $match->setHomeTeamHalftimeScore(1));
        $this->assertSame(1, $match->getHomeTeamHalftimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_away_team_halftime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getAwayTeamHalftimeScore());
        $this->assertSame($match, $match->setAwayTeamHalftimeScore(1));
        $this->assertSame(1, $match->getAwayTeamHalftimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_home_team_fulltime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getHomeTeamFulltimeScore());
        $this->assertSame($match, $match->setHomeTeamFulltimeScore(1));
        $this->assertSame(1, $match->getHomeTeamFulltimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_away_team_fulltime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getAwayTeamFulltimeScore());
        $this->assertSame($match, $match->setAwayTeamFulltimeScore(1));
        $this->assertSame(1, $match->getAwayTeamFulltimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_home_team_extra_halftime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getHomeTeamExtraHalftimeScore());
        $this->assertSame($match, $match->setHomeTeamExtraHalftimeScore(1));
        $this->assertSame(1, $match->getHomeTeamExtraHalftimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_away_team_extra_halftime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getAwayTeamExtraHalftimeScore());
        $this->assertSame($match, $match->setAwayTeamExtraHalftimeScore(1));
        $this->assertSame(1, $match->getAwayTeamExtraHalftimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_home_team_extra_fulltime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getHomeTeamExtraFulltimeScore());
        $this->assertSame($match, $match->setHomeTeamExtraFulltimeScore(1));
        $this->assertSame(1, $match->getHomeTeamExtraFulltimeScore());
    }

    #[Test]
    public function it_has_getter_and_setter_for_away_team_extra_fulltime_score(): void
    {
        $match = new FootballMatch();
        $this->assertNull($match->getAwayTeamExtraFulltimeScore());
        $this->assertSame($match, $match->setAwayTeamExtraFulltimeScore(1));
        $this->assertSame(1, $match->getAwayTeamExtraFulltimeScore());
    }
}

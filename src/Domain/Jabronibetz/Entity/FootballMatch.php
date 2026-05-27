<?php
/*
 * The Doomsday Machine
 * Copyright (C) 2026  Tristan Bonsor
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Domain\Jabronibetz\Entity;

use App\Domain\Jabronibetz\Repository\FootballMatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: FootballMatchRepository::class)]
#[ORM\Table(name: 'football_match')]
#[ORM\UniqueConstraint(
    name: 'UNIQ_IDENTIFIER_FOOTBALL_MATCH_COMPETITION_ID_HOME_TEAM_ID_AWAY_TEAM_ID_ROUND',
    columns: ['competition_id', 'home_team_id', 'away_team_id', 'round']
)]
class FootballMatch
{
    public const string GROUP_LIST = 'football_match_list';
    public const string GROUP_DETAIL = 'football_match_detail';

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $id = null;

    /**
     * @var FootballCompetition|null
     */
    #[ORM\ManyToOne(targetEntity: FootballCompetition::class, inversedBy: 'football_match')]
    #[ORM\JoinColumn(name: 'competition_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?FootballCompetition $competition = null;

    /**
     * @var FootballTeam|null
     */
    #[ORM\ManyToOne(targetEntity: FootballTeam::class, inversedBy: 'football_match')]
    #[ORM\JoinColumn(name: 'home_team_id', nullable: true, onDelete: 'CASCADE')]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?FootballTeam $homeTeam = null;

    /**
     * @var FootballTeam|null
     */
    #[ORM\ManyToOne(targetEntity: FootballTeam::class, inversedBy: 'football_match')]
    #[ORM\JoinColumn(name: 'away_team_id', nullable: true, onDelete: 'CASCADE')]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?FootballTeam $awayTeam = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $timestamp = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Positive]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $round = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'home_team_halftime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $homeTeamHalftimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'away_team_halftime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $awayTeamHalftimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'home_team_fulltime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $homeTeamFulltimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'away_team_fulltime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $awayTeamFulltimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'home_team_extra_halftime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $homeTeamExtraHalftimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'away_team_extra_halftime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $awayTeamExtraHalftimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'home_team_extra_fulltime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $homeTeamExtraFulltimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'away_team_extra_fulltime_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $awayTeamExtraFulltimeScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'home_team_shootout_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $homeTeamShootoutScore = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'away_team_shootout_score', type: Types::SMALLINT, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $awayTeamShootoutScore = null;

    /**
     * @return string
     */
    public function getChoiceValue(): string
    {
        return sprintf(
            '%s vs %s (%s) [%s, Round %s]',
            $this->getHomeTeam()?->getName() ?? 'Unknown',
            $this->getAwayTeam()?->getName() ?? 'Unknown',
            $this->getTimestamp() ?? 'TBD',
            $this->getCompetition()?->getShortName() ?? 'UNK',
            $this->getRound() ?? 'N/A'
        );
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return FootballCompetition|null
     */
    public function getCompetition(): ?FootballCompetition
    {
        return $this->competition;
    }

    /**
     * @param FootballCompetition $cmp
     * @return $this
     */
    public function setCompetition(FootballCompetition $cmp): static
    {
        $this->competition = $cmp;
        return $this;
    }

    /**
     * @return FootballTeam|null
     */
    public function getHomeTeam(): ?FootballTeam
    {
        return $this->homeTeam;
    }

    /**
     * @param FootballTeam|null $team
     * @return $this
     */
    public function setHomeTeam(?FootballTeam $team): static
    {
        $this->homeTeam = $team;
        return $this;
    }

    /**
     * @return FootballTeam|null
     */
    public function getAwayTeam(): ?FootballTeam
    {
        return $this->awayTeam;
    }

    /**
     * @param FootballTeam|null $team
     * @return $this
     */
    public function setAwayTeam(?FootballTeam $team): static
    {
        $this->awayTeam = $team;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp === null ? null : intval($this->timestamp);
    }

    /**
     * @param int|null $timestamp
     * @return $this
     */
    public function setTimestamp(?int $timestamp): static
    {
        $this->timestamp = $timestamp === null ? null : strval($timestamp);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRound(): ?int
    {
        return $this->round;
    }

    /**
     * @param int|null $round
     * @return $this
     */
    public function setRound(?int $round): static
    {
        $this->round = $round === null ? null : $round;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHomeTeamHalftimeScore(): ?int
    {
        return $this->homeTeamHalftimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setHomeTeamHalftimeScore(?int $score): static
    {
        $this->homeTeamHalftimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAwayTeamHalftimeScore(): ?int
    {
        return $this->awayTeamHalftimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setAwayTeamHalftimeScore(?int $score): static
    {
        $this->awayTeamHalftimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHomeTeamFulltimeScore(): ?int
    {
        return $this->homeTeamFulltimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setHomeTeamFulltimeScore(?int $score): static
    {
        $this->homeTeamFulltimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAwayTeamFulltimeScore(): ?int
    {
        return $this->awayTeamFulltimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setAwayTeamFulltimeScore(?int $score): static
    {
        $this->awayTeamFulltimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHomeTeamExtraHalftimeScore(): ?int
    {
        return $this->homeTeamExtraHalftimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setHomeTeamExtraHalftimeScore(?int $score): static
    {
        $this->homeTeamExtraHalftimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAwayTeamExtraHalftimeScore(): ?int
    {
        return $this->awayTeamExtraHalftimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setAwayTeamExtraHalftimeScore(?int $score): static
    {
        $this->awayTeamExtraHalftimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHomeTeamExtraFulltimeScore(): ?int
    {
        return $this->homeTeamExtraFulltimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setHomeTeamExtraFulltimeScore(?int $score): static
    {
        $this->homeTeamExtraFulltimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAwayTeamExtraFulltimeScore(): ?int
    {
        return $this->awayTeamExtraFulltimeScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setAwayTeamExtraFulltimeScore(?int $score): static
    {
        $this->awayTeamExtraFulltimeScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHomeTeamShootoutScore(): ?int
    {
        return $this->homeTeamShootoutScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setHomeTeamShootoutScore(?int $score): static
    {
        $this->homeTeamShootoutScore = $score;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAwayTeamShootoutScore(): ?int
    {
        return $this->awayTeamShootoutScore;
    }

    /**
     * @param int|null $score
     * @return FootballMatch
     */
    public function setAwayTeamShootoutScore(?int $score): static
    {
        $this->awayTeamShootoutScore = $score;
        return $this;
    }
}

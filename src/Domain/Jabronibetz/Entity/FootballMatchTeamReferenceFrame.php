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

use App\Domain\Jabronibetz\Repository\FootballMatchTeamReferenceFrameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: FootballMatchTeamReferenceFrameRepository::class, readOnly: true)]
#[ORM\Table(name: 'view_football_match_team_reference_frame')]
class FootballMatchTeamReferenceFrame
{
    public const string GROUP_LIST = 'football_match_team_reference_frame_list';
    public const string GROUP_DETAIL = 'football_match_team_reference_frame_detail';

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $id = null;

    /**
     * @var FootballMatch|null
     */
    #[ORM\ManyToOne(targetEntity: FootballMatch::class)]
    #[ORM\JoinColumn(name: 'match_id')]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?FootballMatch $sourceMatch = null;

    /**
     * @var FootballCompetition|null
     */
    #[ORM\ManyToOne(targetEntity: FootballCompetition::class)]
    #[ORM\JoinColumn(name: 'competition_id')]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?FootballCompetition $competition = null;

    /**
     * @var FootballTeam|null
     */
    #[ORM\ManyToOne(targetEntity: FootballTeam::class)]
    #[ORM\JoinColumn(name: 'team_id')]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?FootballTeam $team = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::BIGINT)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $timestamp = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $round = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'halftime_goals_for', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $halftimeGoalsFor = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'halftime_goals_against', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $halftimeGoalsAgainst = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'fulltime_goals_for', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $fulltimeGoalsFor = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'fulltime_goals_against', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $fulltimeGoalsAgainst = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'extra_halftime_goals_for', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $extraHalftimeGoalsFor = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'extra_halftime_goals_against', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $extraHalftimeGoalsAgainst = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'extra_fulltime_goals_for', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $extraFulltimeGoalsFor = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'extra_fulltime_goals_against', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $extraFulltimeGoalsAgainst = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'shootout_goals_for', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $shootoutGoalsFor = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'shootout_goals_against', type: Types::SMALLINT)]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $shootoutGoalsAgainst = null;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'home_team', type: Types::BOOLEAN)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?bool $homeTeam = null;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'away_team', type: Types::BOOLEAN)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?bool $awayTeam = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return FootballMatch|null
     */
    public function getSourceMatch(): ?FootballMatch
    {
        return $this->sourceMatch;
    }

    /**
     * @return FootballCompetition|null
     */
    public function getCompetition(): ?FootballCompetition
    {
        return $this->competition;
    }

    /**
     * @return FootballTeam|null
     */
    public function getTeam(): ?FootballTeam
    {
        return $this->team;
    }

    /**
     * @return int|null
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp === null ? null : intval($this->timestamp);
    }

    /**
     * @return int|null
     */
    public function getRound(): ?int
    {
        return $this->round;
    }

    /**
     * @return int|null
     */
    public function getHalftimeGoalsFor(): ?int
    {
        return $this->halftimeGoalsFor;
    }

    /**
     * @return int|null
     */
    public function getHalftimeGoalsAgainst(): ?int
    {
        return $this->halftimeGoalsAgainst;
    }

    /**
     * @return int|null
     */
    public function getFulltimeGoalsFor(): ?int
    {
        return $this->fulltimeGoalsFor;
    }

    /**
     * @return int|null
     */
    public function getFulltimeGoalsAgainst(): ?int
    {
        return $this->fulltimeGoalsAgainst;
    }

    /**
     * @return int|null
     */
    public function getExtraHalftimeGoalsFor(): ?int
    {
        return $this->extraHalftimeGoalsFor;
    }

    /**
     * @return int|null
     */
    public function getExtraHalftimeGoalsAgainst(): ?int
    {
        return $this->extraHalftimeGoalsAgainst;
    }

    /**
     * @return int|null
     */
    public function getExtraFulltimeGoalsFor(): ?int
    {
        return $this->extraFulltimeGoalsFor;
    }

    /**
     * @return int|null
     */
    public function getExtraFulltimeGoalsAgainst(): ?int
    {
        return $this->extraFulltimeGoalsAgainst;
    }

    /**
     * @return int|null
     */
    public function getShootoutGoalsFor(): ?int
    {
        return $this->shootoutGoalsFor;
    }

    /**
     * @return int|null
     */
    public function getShootoutGoalsAgainst(): ?int
    {
        return $this->shootoutGoalsAgainst;
    }

    /**
     * @return bool|null
     */
    public function isHomeTeam(): ?bool
    {
        return $this->homeTeam;
    }

    /**
     * @return bool|null
     */
    public function isAwayTeam(): ?bool
    {
        return $this->awayTeam;
    }
}

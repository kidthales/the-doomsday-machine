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

use App\Domain\Jabronibetz\Repository\FootballCompetitionTeamEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: FootballCompetitionTeamEntryRepository::class)]
#[ORM\Table(name: 'football_competition_team_entry')]
#[ORM\UniqueConstraint(
    name: 'UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_TEAM_ENTRY_COMPETITION_ID_TEAM_ID',
    columns: ['competition_id', 'team_id']
)]
class FootballCompetitionTeamEntry
{
    public const string GROUP_LIST = 'football_competition_entry_list';
    public const string GROUP_DETAIL = 'football_competition_entry_detail';

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
    #[ORM\ManyToOne(targetEntity: FootballCompetition::class, inversedBy: 'football_competition_entry')]
    #[ORM\JoinColumn(name: 'competition_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?FootballCompetition $competition = null;

    /**
     * @var FootballTeam|null
     */
    #[ORM\ManyToOne(targetEntity: FootballTeam::class, inversedBy: 'football_competition_entry')]
    #[ORM\JoinColumn(name: 'team_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?FootballTeam $team = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: '`group`', length: 1)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 1)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $group = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 128)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 128)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $result = null;

    /**
     * @return string
     */
    public function getChoiceValue(): string
    {
        return sprintf(
            '%s - %s',
            $this->getCompetition()?->getChoiceValue() ?? 'Unknown (UNK)',
            $this->getTeam()?->getChoiceValue() ?? 'Unknown (UNK) [unknown]'
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
    public function getTeam(): ?FootballTeam
    {
        return $this->team;
    }

    /**
     * @param FootballTeam $team
     * @return $this
     */
    public function setTeam(FootballTeam $team): static
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     * @return $this
     */
    public function setGroup(?string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * @param string|null $result
     * @return $this
     */
    public function setResult(?string $result): static
    {
        $this->result = $result;
        return $this;
    }
}

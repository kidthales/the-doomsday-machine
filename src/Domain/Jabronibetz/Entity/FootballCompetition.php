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

use App\Domain\Jabronibetz\Repository\FootballCompetitionRepository;
use App\Domain\Shared\Console\Question\ChoosableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: FootballCompetitionRepository::class)]
#[ORM\Table(name: 'football_competition')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_NAME', fields: ['name'])]
class FootballCompetition implements ChoosableInterface
{
    public const string GROUP_LIST = 'football_competition_list';
    public const string GROUP_DETAIL = 'football_competition_detail';

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'short_name', length: 32)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 32)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $shortName = null;

    /**
     * @var FootballOrganization|null
     */
    #[ORM\ManyToOne(targetEntity: FootballOrganization::class, inversedBy: 'football_competition')]
    #[ORM\JoinColumn(name: 'managing_organization_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?FootballOrganization $managingOrganization = null;

    /**
     * @var Collection<int, FootballCompetitionTeamEntry>
     */
    #[ORM\OneToMany(targetEntity: FootballCompetitionTeamEntry::class, mappedBy: 'competition')]
    #[Groups([self::GROUP_DETAIL])]
    private Collection $teamEntries;

    /**
     * @var Collection<int, FootballMatch>
     */
    #[ORM\OneToMany(targetEntity: FootballMatch::class, mappedBy: 'competition')]
    #[Groups([self::GROUP_DETAIL])]
    private Collection $matches;

    /**
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Positive]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $rounds = null;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'group_rounds', type: Types::SMALLINT, nullable: true)]
    #[Assert\Positive]
    #[Groups([self::GROUP_DETAIL])]
    private ?int $groupRounds = null;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: 'separate_match_xg_home_away', type: Types::BOOLEAN, nullable: true)]
    #[Groups([self::GROUP_DETAIL])]
    private ?bool $separateMatchXGHomeAway = null;

    /**
     *
     */
    public function __construct()
    {
        $this->teamEntries = new ArrayCollection();
        $this->matches = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getChoiceKey(): string
    {
        return (string)$this->getId();
    }

    /**
     * @return string
     */
    public function getChoiceValue(): string
    {
        return sprintf('%s (%s)', $this->getName() ?? 'Unknown', $this->getShortName() ?? 'UNK');
    }

    /**
     * @return array<string, Collection<int, FootballTeam>>
     */
    public function getTeamsByGroup(): array
    {
        $teamsByGroup = [];
        foreach ($this->teamEntries as $teamEntry) {
            $group = $teamEntry->getGroup();

            if ($group === null) {
                continue;
            }

            if (!isset($teamsByGroup[$group])) {
                $teamsByGroup[$group] = new ArrayCollection();
            }

            $teamsByGroup[$group]->add($teamEntry->getTeam());
        }
        return $teamsByGroup;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return $this
     */
    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return FootballOrganization|null
     */
    public function getManagingOrganization(): ?FootballOrganization
    {
        return $this->managingOrganization;
    }

    /**
     * @param FootballOrganization $organization
     * @return $this
     */
    public function setManagingOrganization(FootballOrganization $organization): static
    {
        $this->managingOrganization = $organization;
        return $this;
    }

    /**
     * @return Collection<int, FootballCompetitionTeamEntry>
     */
    public function getTeamEntries(): Collection
    {
        return $this->teamEntries;
    }

    /**
     * @return Collection<int, FootballMatch>
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    /**
     * @return int|null
     */
    public function getRounds(): ?int
    {
        return $this->rounds;
    }

    /**
     * @param int|null $rounds
     * @return $this
     */
    public function setRounds(?int $rounds): static
    {
        $this->rounds = $rounds;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getGroupRounds(): ?int
    {
        return $this->groupRounds;
    }

    /**
     * @param int|null $rounds
     * @return $this
     */
    public function setGroupRounds(?int $rounds): static
    {
        $this->groupRounds = $rounds;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getSeparateMatchXgHomeAway(): ?bool
    {
        return $this->separateMatchXGHomeAway;
    }

    /**
     * @param bool|null $separate
     * @return $this
     */
    public function setSeparateMatchXgHomeAway(?bool $separate): static
    {
        $this->separateMatchXGHomeAway = $separate;
        return $this;
    }
}

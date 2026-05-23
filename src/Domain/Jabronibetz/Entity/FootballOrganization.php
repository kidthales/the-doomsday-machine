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

use App\Domain\Jabronibetz\Repository\FootballOrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: FootballOrganizationRepository::class)]
#[ORM\Table(name: 'football_organization')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_FOOTBALL_ORGANIZATION_NAME', fields: ['name'])]
class FootballOrganization
{
    public const string GROUP_CREATE = 'football_organization_create';
    public const string GROUP_LIST = 'football_organization_list';
    public const string GROUP_READ = 'football_organization_read';
    public const string GROUP_UPDATE = 'football_organization_update';
    public const string GROUP_DELETE = 'football_organization_delete';

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        self::GROUP_LIST,
        self::GROUP_READ,
        self::GROUP_UPDATE,
        self::GROUP_DELETE,
        FootballCompetition::GROUP_CREATE,
        FootballCompetition::GROUP_READ,
        FootballCompetition::GROUP_UPDATE,
        FootballCompetition::GROUP_DELETE,
        FootballTeam::GROUP_CREATE,
        FootballTeam::GROUP_READ,
        FootballTeam::GROUP_UPDATE,
        FootballTeam::GROUP_DELETE
    ])]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 255)]
    #[Groups([
        self::GROUP_CREATE,
        self::GROUP_LIST,
        self::GROUP_READ,
        self::GROUP_UPDATE,
        self::GROUP_DELETE,
        FootballCompetition::GROUP_CREATE,
        FootballCompetition::GROUP_READ,
        FootballCompetition::GROUP_UPDATE,
        FootballCompetition::GROUP_DELETE,
        FootballTeam::GROUP_CREATE,
        FootballTeam::GROUP_READ,
        FootballTeam::GROUP_UPDATE,
        FootballTeam::GROUP_DELETE
    ])]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'short_name', length: 32)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 32)]
    #[Groups([
        self::GROUP_CREATE,
        self::GROUP_LIST,
        self::GROUP_READ,
        self::GROUP_UPDATE,
        self::GROUP_DELETE,
        FootballCompetition::GROUP_CREATE,
        FootballCompetition::GROUP_READ,
        FootballCompetition::GROUP_UPDATE,
        FootballCompetition::GROUP_DELETE,
        FootballTeam::GROUP_CREATE,
        FootballTeam::GROUP_READ,
        FootballTeam::GROUP_UPDATE,
        FootballTeam::GROUP_DELETE
    ])]
    private ?string $shortName = null;

    /**
     * @var Collection<int, FootballCompetition>
     */
    #[ORM\OneToMany(targetEntity: FootballCompetition::class, mappedBy: 'managingOrganization')]
    #[Groups([
        self::GROUP_READ,
        self::GROUP_DELETE
    ])]
    private Collection $managedCompetitions;

    /**
     * @var Collection<int, FootballTeam>
     */
    #[ORM\OneToMany(targetEntity: FootballTeam::class, mappedBy: 'managingOrganization')]
    #[Groups([
        self::GROUP_READ,
        self::GROUP_DELETE
    ])]
    private Collection $managedTeams;

    /**
     *
     */
    public function __construct()
    {
        $this->managedCompetitions = new ArrayCollection();
        $this->managedTeams = new ArrayCollection();
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
     * @return Collection<int, FootballCompetition>
     */
    public function getManagedCompetitions(): Collection
    {
        return $this->managedCompetitions;
    }

    /**
     * @return Collection<int, FootballTeam>
     */
    public function getManagedTeams(): Collection
    {
        return $this->managedTeams;
    }
}

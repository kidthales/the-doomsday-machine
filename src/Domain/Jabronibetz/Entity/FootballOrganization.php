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
use App\Domain\Shared\Entity\ChoosableEntityInterface;
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
class FootballOrganization implements ChoosableEntityInterface
{
    public const string GROUP_LIST = 'football_organization_list';
    public const string GROUP_DETAIL = 'football_organization_detail';

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
     * @var Collection<int, FootballCompetition>
     */
    #[ORM\OneToMany(targetEntity: FootballCompetition::class, mappedBy: 'managingOrganization')]
    #[Groups([self::GROUP_DETAIL])]
    private Collection $managedCompetitions;

    /**
     * @var Collection<int, FootballTeam>
     */
    #[ORM\OneToMany(targetEntity: FootballTeam::class, mappedBy: 'managingOrganization')]
    #[Groups([self::GROUP_DETAIL])]
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

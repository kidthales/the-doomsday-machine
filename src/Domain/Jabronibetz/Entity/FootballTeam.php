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

use App\Domain\Jabronibetz\Enum\FootballGender;
use App\Domain\Jabronibetz\Repository\FootballTeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: FootballTeamRepository::class)]
#[ORM\Table(name: 'football_team')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_FOOTBALL_TEAM_NAME_GENDER', fields: ['name', 'gender'])]
class FootballTeam
{
    public const string GROUP_CREATE = 'football_team_create';
    public const string GROUP_LIST = 'football_team_list';
    public const string GROUP_READ = 'football_team_read';
    public const string GROUP_UPDATE = 'football_team_update';
    public const string GROUP_DELETE = 'football_team_delete';

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
        FootballOrganization::GROUP_READ,
        FootballOrganization::GROUP_DELETE
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
        FootballOrganization::GROUP_READ,
        FootballOrganization::GROUP_DELETE
    ])]
    private ?string $name = null;

    /**
     * @var FootballGender|null
     */
    #[ORM\Column(type: 'string', enumType: FootballGender::class)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Groups([
        self::GROUP_CREATE,
        self::GROUP_LIST,
        self::GROUP_READ,
        self::GROUP_UPDATE,
        self::GROUP_DELETE,
        FootballOrganization::GROUP_READ,
        FootballOrganization::GROUP_DELETE
    ])]
    private ?FootballGender $gender = null;

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
        FootballOrganization::GROUP_READ,
        FootballOrganization::GROUP_DELETE
    ])]
    private ?string $shortName = null;

    /**
     * @var FootballOrganization|null
     */
    #[ORM\ManyToOne(targetEntity: FootballOrganization::class, inversedBy: 'football_team')]
    #[ORM\JoinColumn(name: 'managing_organization_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([
        self::GROUP_CREATE,
        self::GROUP_READ,
        self::GROUP_UPDATE,
        self::GROUP_DELETE
    ])]
    private ?FootballOrganization $managingOrganization = null;

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
     * @return FootballGender|null
     */
    public function getGender(): ?FootballGender
    {
        return $this->gender;
    }

    /**
     * @param FootballGender $gender
     * @return $this
     */
    public function setGender(FootballGender $gender): static
    {
        $this->gender = $gender;
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
}

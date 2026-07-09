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

namespace App\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Repository\RulesWeaponRangeCategoryDistanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: RulesWeaponRangeCategoryDistanceRepository::class)]
#[ORM\Table(name: 'rules_weapon_range_category_distance')]
#[ORM\UniqueConstraint(
    name: 'UNIQ_IDENTIFIER_RULES_WEAPON_RANGE_CATEGORY_DISTANCE_WEAPON_ID_RANGE_CATEGORY_ID',
    columns: ['rules_weapon_id', 'rules_range_category_id']
)]
class RulesWeaponRangeCategoryDistance
{
    public const string GROUP_LIST = 'rules_weapon_range_category_distance_list';
    public const string GROUP_DETAIL = 'rules_weapon_range_category_distance_detail';

    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $id = null;

    /**
     * @var RulesWeapon|null
     */
    #[ORM\ManyToOne(targetEntity: RulesWeapon::class, inversedBy: 'rules_weapon_range_category_distance')]
    #[ORM\JoinColumn(name: 'rules_weapon_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesWeapon $weapon = null;

    /**
     * @var RulesRangeCategory|null
     */
    #[ORM\ManyToOne(targetEntity: RulesRangeCategory::class, inversedBy: 'rules_weapon_range_category_distance')]
    #[ORM\JoinColumn(name: 'rules_range_category_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesRangeCategory $rangeCategory = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\Positive]
    #[Assert\NotNull]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $distance = null;

    /**
     * @var RulesSource|null
     */
    #[ORM\ManyToOne(targetEntity: RulesSource::class, inversedBy: 'rules_weapon_range_category_distance')]
    #[ORM\JoinColumn(name: 'rules_source_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesSource $source = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return RulesWeapon|null
     */
    public function getWeapon(): ?RulesWeapon
    {
        return $this->weapon;
    }

    /**
     * @return string|null
     */
    #[Groups([self::GROUP_LIST])]
    public function getWeaponName(): ?string
    {
        return $this->weapon?->getName();
    }

    /**
     * @param RulesWeapon $weapon
     * @return $this
     */
    public function setWeapon(RulesWeapon $weapon): static
    {
        $this->weapon = $weapon;
        return $this;
    }

    /**
     * @return RulesRangeCategory|null
     */
    public function getRangeCategory(): ?RulesRangeCategory
    {
        return $this->rangeCategory;
    }

    /**
     * @return string|null
     */
    #[Groups([self::GROUP_LIST])]
    public function getRangeCategoryName(): ?string
    {
        return $this->rangeCategory?->getName();
    }

    /**
     * @param RulesRangeCategory $rangeCategory
     * @return $this
     */
    public function setRangeCategory(RulesRangeCategory $rangeCategory): static
    {
        $this->rangeCategory = $rangeCategory;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDistance(): ?int
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     * @return $this
     */
    public function setDistance(int $distance): static
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * @return RulesSource|null
     */
    public function getSource(): ?RulesSource
    {
        return $this->source;
    }

    /**
     * @param RulesSource $source
     * @return $this
     */
    public function setSource(RulesSource $source): static
    {
        $this->source = $source;
        return $this;
    }
}

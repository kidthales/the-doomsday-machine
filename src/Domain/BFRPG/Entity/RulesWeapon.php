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

use App\Domain\BFRPG\Repository\RulesWeaponRepository;
use App\Domain\Shared\Validator\DiceRollFormat;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: RulesWeaponRepository::class)]
#[ORM\Table(name: 'rules_weapon')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RULES_WEAPON_NAME', fields: ['name'])]
class RulesWeapon
{
    public const string GROUP_LIST = 'rules_weapon_list';
    public const string GROUP_DETAIL = 'rules_weapon_detail';

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
    #[ORM\Column(length: 128)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 128)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $name = null;

    /**
     * @var float|null
     */
    #[ORM\Column(precision: 5, scale: 2)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Assert\LessThan(value: 1000)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?float $price = null;

    /**
     * @var float|null
     */
    #[ORM\Column(precision: 5, scale: 2)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[Assert\LessThan(value: 1000)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?float $weight = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Groups([self::GROUP_DETAIL])]
    private ?string $description = null;

    /**
     * @var RulesWeaponSize|null
     */
    #[ORM\ManyToOne(targetEntity: RulesWeaponSize::class, inversedBy: 'rules_weapon')]
    #[ORM\JoinColumn(name: 'rules_weapon_size_id', nullable: true, onDelete: 'CASCADE')]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesWeaponSize $weaponSize = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'damage_roll', length: 16, nullable: true)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Assert\Length(min: 3, max: 16)]
    #[DiceRollFormat]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $damageRoll = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'missile_damage_roll', length: 16, nullable: true)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Assert\Length(min: 3, max: 16)]
    #[DiceRollFormat]
    #[Groups([self::GROUP_DETAIL])]
    private ?string $missileDamageRoll = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'one_handed_damage_roll', length: 16, nullable: true)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Assert\Length(min: 3, max: 16)]
    #[DiceRollFormat]
    #[Groups([self::GROUP_DETAIL])]
    private ?string $oneHandedDamageRoll = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'two_handed_damage_roll', length: 16, nullable: true)]
    #[Assert\NotBlank(allowNull: true, normalizer: 'trim')]
    #[Assert\Length(min: 3, max: 16)]
    #[DiceRollFormat]
    #[Groups([self::GROUP_DETAIL])]
    private ?string $twoHandedDamageRoll = null;

    /**
     * @var RulesWeaponCategory|null
     */
    #[ORM\ManyToOne(targetEntity: RulesWeaponCategory::class, inversedBy: 'rules_weapon')]
    #[ORM\JoinColumn(name: 'rules_weapon_category_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesWeaponCategory $weaponCategory = null;

    /**
     * @var RulesSource|null
     */
    #[ORM\ManyToOne(targetEntity: RulesSource::class, inversedBy: 'rules_weapon')]
    #[ORM\JoinColumn(name: 'rules_source_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesSource $source = null;

    /**
     * @var Collection<int, RulesWeaponRangeCategoryDistance>
     */
    #[ORM\OneToMany(targetEntity: RulesWeaponRangeCategoryDistance::class, mappedBy: 'weapon')]
    private Collection $weaponRangeCategoryDistances;

    /**
     *
     */
    public function __construct()
    {
        $this->weaponRangeCategoryDistances = new ArrayCollection();
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
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     * @return $this
     */
    public function setWeight(float $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return RulesWeaponSize|null
     */
    public function getWeaponSize(): ?RulesWeaponSize
    {
        return $this->weaponSize;
    }

    /**
     * @param RulesWeaponSize|null $weaponSize
     * @return $this
     */
    public function setWeaponSize(?RulesWeaponSize $weaponSize): static
    {
        $this->weaponSize = $weaponSize;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDamageRoll(): ?string
    {
        return $this->damageRoll;
    }

    /**
     * @param string|null $damageRoll
     * @return $this
     */
    public function setDamageRoll(?string $damageRoll): static
    {
        $this->damageRoll = $damageRoll;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMissileDamageRoll(): ?string
    {
        return $this->missileDamageRoll;
    }

    /**
     * @param string|null $damageRoll
     * @return $this
     */
    public function setMissileDamageRoll(?string $damageRoll): static
    {
        $this->missileDamageRoll = $damageRoll;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOneHandedDamageRoll(): ?string
    {
        return $this->oneHandedDamageRoll;
    }

    /**
     * @param string|null $damageRoll
     * @return $this
     */
    public function setOneHandedDamageRoll(?string $damageRoll): static
    {
        $this->oneHandedDamageRoll = $damageRoll;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTwoHandedDamageRoll(): ?string
    {
        return $this->twoHandedDamageRoll;
    }

    /**
     * @param string|null $damageRoll
     * @return $this
     */
    public function setTwoHandedDamageRoll(?string $damageRoll): static
    {
        $this->twoHandedDamageRoll = $damageRoll;
        return $this;
    }

    /**
     * @return RulesWeaponCategory|null
     */
    public function getWeaponCategory(): ?RulesWeaponCategory
    {
        return $this->weaponCategory;
    }

    /**
     * @param RulesWeaponCategory $weaponCategory
     * @return $this
     */
    public function setWeaponCategory(RulesWeaponCategory $weaponCategory): static
    {
        $this->weaponCategory = $weaponCategory;
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

    /**
     * @return Collection<int, RulesWeaponRangeCategoryDistance>
     */
    public function getWeaponRangeCategoryDistances(): Collection
    {
        return $this->weaponRangeCategoryDistances;
    }
}

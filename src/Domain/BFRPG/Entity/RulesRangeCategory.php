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

use App\Domain\BFRPG\Repository\RulesRangeCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: RulesRangeCategoryRepository::class)]
#[ORM\Table(name: 'rules_range_category')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RULES_RANGE_CATEGORY_NAME', fields: ['name'])]
class RulesRangeCategory
{
    public const string GROUP_LIST = 'rules_range_category_list';
    public const string GROUP_DETAIL = 'rules_range_category_detail';

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
    #[ORM\Column(length: 16)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 16)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $name = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?int $modifier = null;

    /**
     * @var RulesSource|null
     */
    #[ORM\ManyToOne(targetEntity: RulesSource::class, inversedBy: 'rules_range_category')]
    #[ORM\JoinColumn(name: 'rules_source_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesSource $source = null;

    /**
     * @var Collection<int, RulesItemRangeCategoryDistance>
     */
    #[ORM\OneToMany(targetEntity: RulesItemRangeCategoryDistance::class, mappedBy: 'rangeCategory')]
    private Collection $itemRangeCategoryDistances;

    /**
     * @var Collection<int, RulesWeaponRangeCategoryDistance>
     */
    #[ORM\OneToMany(targetEntity: RulesWeaponRangeCategoryDistance::class, mappedBy: 'rangeCategory')]
    private Collection $weaponRangeCategoryDistances;

    /**
     *
     */
    public function __construct()
    {
        $this->itemRangeCategoryDistances = new ArrayCollection();
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
     * @return int|null
     */
    public function getModifier(): ?int
    {
        return $this->modifier;
    }

    /**
     * @param int $modifier
     * @return $this
     */
    public function setModifier(int $modifier): static
    {
        $this->modifier = $modifier;
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
     * @return Collection<int, RulesItemRangeCategoryDistance>
     */
    public function getItemRangeCategoryDistances(): Collection
    {
        return $this->itemRangeCategoryDistances;
    }

    /**
     * @return Collection<int, RulesWeaponRangeCategoryDistance>
     */
    public function getWeaponRangeCategoryDistances(): Collection
    {
        return $this->weaponRangeCategoryDistances;
    }
}

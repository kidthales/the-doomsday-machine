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

use App\Domain\BFRPG\Repository\RulesItemRepository;
use App\Domain\Shared\Console\Question\ChoosableInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: RulesItemRepository::class)]
#[ORM\Table(name: 'rules_item')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RULES_ITEM_NAME', fields: ['name'])]
class RulesItem implements ChoosableInterface
{
    public const string GROUP_LIST = 'rules_item_list';
    public const string GROUP_DETAIL = 'rules_item_detail';

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
     * @var RulesSource|null
     */
    #[ORM\ManyToOne(targetEntity: RulesSource::class, inversedBy: 'rules_item')]
    #[ORM\JoinColumn(name: 'rules_source_id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups([self::GROUP_DETAIL])]
    private ?RulesSource $source = null;

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
        return $this->getName();
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

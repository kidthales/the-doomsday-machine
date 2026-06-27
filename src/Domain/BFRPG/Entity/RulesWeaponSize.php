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

use App\Domain\BFRPG\Repository\RulesWeaponSizeRepository;
use App\Domain\Shared\Console\Question\ChoosableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: RulesWeaponSizeRepository::class)]
#[ORM\Table(name: 'rules_weapon_size')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RULES_WEAPON_SIZE_NAME', fields: ['name'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RULES_WEAPON_SIZE_SHORT_NAME', fields: ['shortName'])]
class RulesWeaponSize implements ChoosableInterface
{
    public const string GROUP_LIST = 'rules_weapon_size_list';
    public const string GROUP_DETAIL = 'rules_weapon_size_detail';

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
     * @var string|null
     */
    #[ORM\Column(name: 'short_name', length: 1)]
    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\Length(min: 1, max: 1)]
    #[Groups([self::GROUP_LIST, self::GROUP_DETAIL])]
    private ?string $shortName = null;

    /**
     * @var RulesSource|null
     */
    #[ORM\ManyToOne(targetEntity: RulesSource::class, inversedBy: 'rules_weapon_size')]
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

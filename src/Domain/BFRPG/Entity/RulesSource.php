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

use App\Domain\BFRPG\Repository\RulesSourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[ORM\Entity(repositoryClass: RulesSourceRepository::class)]
#[ORM\Table(name: 'rules_source')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_RULES_SOURCE_NAME', fields: ['name'])]
class RulesSource
{
    public const string GROUP_CREATE = 'rules_source_create';
    public const string GROUP_LIST = 'rules_source_list';
    public const string GROUP_READ = 'rules_source_read';
    public const string GROUP_UPDATE = 'rules_source_update';
    public const string GROUP_DELETE = 'rules_source_delete';

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
    ])]
    private ?string $name = null;

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
}

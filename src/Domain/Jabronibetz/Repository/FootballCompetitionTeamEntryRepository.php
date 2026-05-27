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

namespace App\Domain\Jabronibetz\Repository;

use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FootballCompetitionTeamEntry>
 */
final class FootballCompetitionTeamEntryRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FootballCompetitionTeamEntry::class);
    }

    /**
     * @return array<string, string>
     */
    public function findAllChoices(): array
    {
        return array_reduce(
            $this->findAll(),
            function (array $entries, FootballCompetitionTeamEntry $entry) {
                $entries[(string)$entry->getId()] = $entry->getChoiceValue();
                return $entries;
            },
            []
        );
    }
}

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

namespace App\Provider\FootyStats;

use App\Scraper\FootyStatsScraperAwareTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class ScraperTargetArgumentsProvider implements TargetArgumentsProviderInterface
{
    use FootyStatsScraperAwareTrait;

    public function getNations(): array
    {
        return $this->footyStatsScraper->getNations();
    }

    public function getCompetitions(string $nation): array
    {
        return $this->footyStatsScraper->getCompetitions($nation);
    }

    /**
     * @param string $nation
     * @param string $competition
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getSeasons(string $nation, string $competition): array
    {
        $availableSeasons = $this->footyStatsScraper->scrapeAvailableSeasons($nation, $competition);
        return [$availableSeasons['current'], ...array_keys($availableSeasons['previous']['overview'])];
    }
}

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

namespace App\Console\Command\FootyStats;

use App\Entity\FootyStats\Target;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait TargetOptionsChoicesTrait
{
    protected function configureTargetOptions(): self
    {
        $this
            ->addOption('nation', mode: InputOption::VALUE_REQUIRED, description: 'Nation choice')
            ->addOption('competition', mode: InputOption::VALUE_REQUIRED, description: 'Competition choice')
            ->addOption('season', mode: InputOption::VALUE_REQUIRED, description: 'Season choice');

        return $this;
    }

    /**
     * @param InputInterface $input
     * @return Target
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function promptTargetChoices(InputInterface $input): Target
    {
        $nations = $this->scraper->getNations();
        $nationChoice  = $input->getOption('nation');

        if (!$nationChoice) {
            $nationChoice = $this->io->choice('Choose Nation', $nations);
        } else if (!in_array($nationChoice, $nations)) {
            throw new RuntimeException(sprintf('Invalid nation choice: %s', $nationChoice));
        }

        $competitions = $this->scraper->getCompetitions($nationChoice);
        $competitionChoice  = $input->getOption('competition');

        if (!$competitionChoice) {
            $competitionChoice = $this->io->choice('Choose Competition', $competitions);
        } else if (!in_array($competitionChoice, $competitions)) {
            throw new RuntimeException(sprintf('Invalid competition choice: %s', $competitionChoice));
        }

        $availableSeasons = $this->scraper->scrapeAvailableSeasons($nationChoice, $competitionChoice);
        $seasons = [$availableSeasons['current'], ...array_keys($availableSeasons['previous']['overview'])];
        $seasonChoice  = $input->getOption('season');

        if (!$seasonChoice) {
            $seasonChoice = $this->io->choice('Choose Season', $seasons);
        } else if (!in_array($seasonChoice, $seasons)) {
            throw new RuntimeException(sprintf('Invalid season choice: %s', $seasonChoice));
        }

        return new Target($nationChoice, $competitionChoice, $seasonChoice);
    }
}

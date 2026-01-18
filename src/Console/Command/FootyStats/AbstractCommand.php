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

use App\Console\Command\AbstractCommand as Command;
use App\Entity\FootyStats\Target;
use App\Provider\FootyStats\TargetArgumentsProviderInterface;
use App\Scraper\FootyStatsScraperAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class AbstractCommand extends Command
{
    use FootyStatsScraperAwareTrait;

    public const int SUCCESS = Command::SUCCESS;
    public const int FAILURE = Command::FAILURE;
    public const int INVALID = Command::INVALID;

    protected TargetArgumentsProviderInterface $targetArgumentsProvider;

    #[Required]
    public function setTargetArgumentsProvider(
        #[Autowire(service: 'app.provider.footy_stats.database_target_arguments_provider')]
        TargetArgumentsProviderInterface $provider
    ): void
    {
        $this->targetArgumentsProvider = $provider;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nation', InputArgument::REQUIRED, 'Nation name')
            ->addArgument('competition', InputArgument::REQUIRED, 'Competition name')
            ->addArgument('season', InputArgument::REQUIRED, 'Season identifier');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $nation = $input->getArgument('nation');
        $nations = $this->targetArgumentsProvider->getNations();

        if (!$nation) {
            sort($nations);
            $nation = $this->io->choice('Choose Nation', $nations);
            $input->setArgument('nation', $nation);
        } else if (!in_array($nation, $nations)) {
            throw new RuntimeException(sprintf('Invalid nation argument: %s', $nation));
        }

        $competition = $input->getArgument('competition');
        $competitions = $this->targetArgumentsProvider->getCompetitions($nation);

        if (!$competition) {
            sort($competitions);
            $competition = $this->io->choice('Choose Competition', $competitions);
            $input->setArgument('competition', $competition);
        } else if (!in_array($competition, $competitions)) {
            throw new RuntimeException(sprintf('Invalid competition argument: %s', $competition));
        }

        $season = $input->getArgument('season');
        $seasons = $this->targetArgumentsProvider->getSeasons($nation, $competition);

        if (!$season) {
            sort($seasons);
            $input->setArgument('season', $this->io->choice('Choose Season', $seasons));
        } else if (!in_array($season, $seasons)) {
            throw new RuntimeException(sprintf('Invalid season argument: %s', $season));
        }
    }

    final protected function getTargetArguments(InputInterface $input): Target
    {
        return new Target(
            $input->getArgument('nation'),
            $input->getArgument('competition'),
            $input->getArgument('season')
        );
    }
}

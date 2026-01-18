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

namespace App\Command\FootyStats\Migrations;

use App\Console\Command\FootyStats\AbstractCommand as Command;
use App\Database\FootyStats\AwayTeamStandingView;
use App\Database\FootyStats\AwayTeamStandingViewAwareTrait;
use App\Database\FootyStats\HomeTeamStandingView;
use App\Database\FootyStats\HomeTeamStandingViewAwareTrait;
use App\Database\FootyStats\MatchTable;
use App\Database\FootyStats\MatchTableAwareTrait;
use App\Database\FootyStats\MatchXgView;
use App\Database\FootyStats\MatchXgViewAwareTrait;
use App\Database\FootyStats\TeamStandingView;
use App\Database\FootyStats\TeamStandingViewAwareTrait;
use App\Database\FootyStats\TeamStrengthView;
use App\Database\FootyStats\TeamStrengthViewAwareTrait;
use App\Migrations\FootyStatsMigrationGenerator;
use App\Provider\FootyStats\TargetArgumentsProviderInterface;
use Doctrine\DBAL\Exception as DBALException;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @deprecated
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:migrations:generate',
    description: 'Generate a Footy Stats migration class'
)]
final class GenerateCommand extends Command
{
    use AwayTeamStandingViewAwareTrait,
        HomeTeamStandingViewAwareTrait,
        MatchTableAwareTrait,
        MatchXgViewAwareTrait,
        TeamStandingViewAwareTrait,
        TeamStrengthViewAwareTrait;

    private FootyStatsMigrationGenerator $migrationGenerator;

    #[Required]
    public function setMigrationGenerator(FootyStatsMigrationGenerator $generator): void
    {
        $this->migrationGenerator = $generator;
    }

    public function setTargetArgumentsProvider(
        #[Autowire(service: 'app.provider.footy_stats.scraper_target_arguments_provider')]
        TargetArgumentsProviderInterface $provider
    ): void
    {
        parent::setTargetArgumentsProvider($provider);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ClientExceptionInterface
     * @throws DBALException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Generate Footy Stats Migration');

        $target = $this->getTargetArguments($input);
        $this->io->info((string)$target);

        $up = [];
        $down = [];

        if (!$this->matchTable->exists($target)) {
            $up[] = MatchTable::getCreateSql($target);
            array_unshift($down, MatchTable::getDropSql($target));
        }

        if (!$this->teamStandingView->exists($target)) {
            $up[] = TeamStandingView::getCreateSql($target);
            array_unshift($down, TeamStandingView::getDropSql($target));
        }

        if (!$this->homeTeamStandingView->exists($target)) {
            $up[] = HomeTeamStandingView::getCreateSql($target);
            array_unshift($down, HomeTeamStandingView::getDropSql($target));
        }

        if (!$this->awayTeamStandingView->exists($target)) {
            $up[] = AwayTeamStandingView::getCreateSql($target);
            array_unshift($down, AwayTeamStandingView::getDropSql($target));
        }

        if (!$this->teamStrengthView->exists($target)) {
            $up[] = TeamStrengthView::getCreateSql($target);
            array_unshift($down, TeamStrengthView::getDropSql($target));
        }

        if (!$this->matchXgView->exists($target)) {
            $up[] = MatchXgView::getCreateSql($target);
            array_unshift($down, MatchXgView::getDropSql($target));
        }

        // @codeCoverageIgnoreStart
        if (count($up) !== count($down)) {
            throw new LogicException(sprintf('Up (%d) and down (%d) change counts must match', count($up), count($down)));
        }
        // @codeCoverageIgnoreEnd

        if (count($up) === 0) {
            $this->io->success('Nothing to migrate, all tables and views exist');
            return Command::SUCCESS;
        }

        $this->io->success('Migration written to ' . $this->migrationGenerator->generate($up, $down, (string)$target));

        return Command::SUCCESS;
    }
}

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

use App\Command\FootyStats\TargetOptionsChoicesTrait;
use App\FootyStats\Database\AwayTeamStandingView;
use App\FootyStats\Database\HomeTeamStandingView;
use App\FootyStats\Database\MatchTable;
use App\FootyStats\Database\MatchTableAwareTrait;
use App\FootyStats\Database\MatchXgView;
use App\FootyStats\Database\TeamStandingView;
use App\FootyStats\Database\TeamStrengthView;
use App\FootyStats\MigrationGenerator;
use App\FootyStats\Scraper;
use App\FootyStats\ScraperAwareTrait;
use Doctrine\DBAL\Exception as DBALException;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:migrations:generate',
    description: 'Generate a Footy Stats migration class'
)]
final class GenerateCommand extends Command
{
    use MatchTableAwareTrait, ScraperAwareTrait, TargetOptionsChoicesTrait;

    private MigrationGenerator $migrationGenerator;
    private TeamStandingView $teamStandingView;
    private HomeTeamStandingView $homeTeamStandingView;
    private AwayTeamStandingView $awayTeamStandingView;
    private TeamStrengthView $teamStrengthView;
    private MatchXgView $matchXgView;

    #[Required]
    public function setMigrationGenerator(MigrationGenerator $generator): void
    {
        $this->migrationGenerator = $generator;
    }

    #[Required]
    public function setScraper(Scraper $scraper): void
    {
        $this->scraper = $scraper;
    }

    #[Required]
    public function setViews(
        TeamStandingView $teamStandingView,
        HomeTeamStandingView $homeTeamStandingView,
        AwayTeamStandingView $awayTeamStandingView,
        TeamStrengthView $teamStrengthView,
        MatchXgView $matchXgView,
    ): void
    {
        $this->teamStandingView = $teamStandingView;
        $this->homeTeamStandingView = $homeTeamStandingView;
        $this->awayTeamStandingView = $awayTeamStandingView;
        $this->teamStrengthView = $teamStrengthView;
        $this->matchXgView = $matchXgView;
    }

    protected function configure(): void
    {
        $this->configureTargetOptions();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ClientExceptionInterface
     * @throws DBALException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Generate Footy Stats Migration');

        $target = $this->promptTargetChoices($input);
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

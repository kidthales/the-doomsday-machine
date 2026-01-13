<?php

namespace App\Command\FootyStats\Migrations;

use App\FootyStats\Database\AwayTeamStandingView;
use App\FootyStats\Database\HomeTeamStandingView;
use App\FootyStats\Database\MatchTable;
use App\FootyStats\Database\MatchXgView;
use App\FootyStats\Database\TeamStandingView;
use App\FootyStats\Database\TeamStrengthView;
use App\FootyStats\MigrationGenerator;
use App\FootyStats\Scraper;
use App\FootyStats\Target;
use Doctrine\DBAL\Exception as DBALException;
use LogicException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    private MigrationGenerator $migrationGenerator;
    private Scraper $scraper;

    private MatchTable $matchTable;
    private TeamStandingView $teamStandingView;
    private HomeTeamStandingView $homeTeamStandingView;
    private AwayTeamStandingView $awayTeamStandingView;
    private TeamStrengthView $teamStrengthView;
    private MatchXgView $matchXgView;

    private SymfonyStyle $io;

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
    public function setDatabaseAccessors(
        MatchTable $matchTable,
        TeamStandingView $teamStandingView,
        HomeTeamStandingView $homeTeamStandingView,
        AwayTeamStandingView $awayTeamStandingView,
        TeamStrengthView $teamStrengthView,
        MatchXgView $matchXgView,
    ): void
    {
        $this->matchTable = $matchTable;
        $this->teamStandingView = $teamStandingView;
        $this->homeTeamStandingView = $homeTeamStandingView;
        $this->awayTeamStandingView = $awayTeamStandingView;
        $this->teamStrengthView = $teamStrengthView;
        $this->matchXgView = $matchXgView;
    }

    protected function configure(): void
    {
        $this
            ->addOption('blank', mode: InputOption::VALUE_NONE, description: 'Generate a blank migration class')
            ->addOption('nation', mode: InputOption::VALUE_REQUIRED, description: 'Nation choice')
            ->addOption('competition', mode: InputOption::VALUE_REQUIRED, description: 'Competition choice')
            ->addOption('season', mode: InputOption::VALUE_REQUIRED, description: 'Season choice');
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
        $this->io = new SymfonyStyle($input, $output);

        if ($input->getOption('blank')) {
            $this->io->success('Migration written to ' . $this->migrationGenerator->generate([], [], ''));
            return Command::SUCCESS;
        }

        $target = $this->promptChoices($input);
        $this->io->info($target);

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

        $this->io->success('Migration written to ' . $this->migrationGenerator->generate($up, $down, $target));

        return Command::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @return Target
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function promptChoices(InputInterface $input): Target
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

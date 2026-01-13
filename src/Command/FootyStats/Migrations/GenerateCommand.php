<?php

namespace App\Command\FootyStats\Migrations;

use App\FootyStats\MigrationGenerator;
use App\FootyStats\Scraper;
use App\FootyStats\Target;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
    private Scraper $scraper;
    private MigrationGenerator $migrationGenerator;
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

    protected function configure(): void
    {
        $this
            ->addOption('blank', mode: InputOption::VALUE_NONE, description: 'Generate a blank migration class')
            ->addOption('nation', mode: InputOption::VALUE_REQUIRED, description: 'Nation choice')
            ->addOption('competition', mode: InputOption::VALUE_REQUIRED, description: 'Competition choice')
            ->addOption('season', mode: InputOption::VALUE_REQUIRED, description: 'Season choice')
            ->addOption('dry-run', mode: InputOption::VALUE_NONE, description: 'Output migration class to stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if ($input->getOption('blank')) {
            return $this->generateBlank();
        }

        $target = $this->promptChoices($input);

        $this->io->info($target);

        return Command::SUCCESS;
    }

    private function generateBlank(): int
    {
        $path = $this->migrationGenerator->generate([], [], '');
        $this->io->success('Migration written to ' . $path);
        return Command::SUCCESS;
    }

    private function promptChoices(InputInterface $input): Target
    {
        $nations = $this->scraper->getNations();
        $nationChoice  = $input->getOption('nation');

        if (!$nationChoice) {
            $nationChoice = $this->io->choice('Choose Nation', $nations);
        } else if (!in_array($nationChoice, $nations)) {
            // TODO
            throw new RuntimeException();
        }

        $competitions = $this->scraper->getCompetitions($nationChoice);
        $competitionChoice  = $input->getOption('competition');

        if (!$competitionChoice) {
            $competitionChoice = $this->io->choice('Choose Competition', $competitions);
        } else if (!in_array($competitionChoice, $competitions)) {
            // TODO
            throw new RuntimeException();
        }

        $availableSeasons = $this->scraper->scrapeAvailableSeasons($nationChoice, $competitionChoice);
        $seasons = [$availableSeasons['current'], ...array_keys($availableSeasons['previous']['overview'])];
        $seasonChoice  = $input->getOption('season');

        if (!$seasonChoice) {
            $seasonChoice = $this->io->choice('Choose Season', $seasons);
        } else if (!in_array($seasonChoice, $seasons)) {
            // TODO
            throw new RuntimeException();
        }

        return new Target($nationChoice, $competitionChoice, $seasonChoice);
    }
}

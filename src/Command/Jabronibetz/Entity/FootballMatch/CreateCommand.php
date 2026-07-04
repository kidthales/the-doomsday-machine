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

namespace App\Command\Jabronibetz\Entity\FootballMatch;

use App\Domain\Jabronibetz\Console\Command\Command;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:entity:football-match:create',
    description: 'Create a football match'
)]
final class CreateCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'competition-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football competition for the match'
            )
            ->addOption(
                name: 'home-team-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the home football team in the match'
            )
            ->addOption(
                name: 'away-team-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the away football team in the match'
            )
            ->addOption(
                name: 'timestamp',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The timestamp for the football match'
            )
            ->addOption(
                name: 'round',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The round of the football match'
            )
            ->addOption(
                name: 'home-team-halftime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The home football team score at halftime'
            )
            ->addOption(
                name: 'away-team-halftime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The away football team score at halftime'
            )
            ->addOption(
                name: 'home-team-fulltime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The home football team score at fulltime'
            )
            ->addOption(
                name: 'away-team-fulltime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The away football team score at fulltime'
            )
            ->addOption(
                name: 'home-team-extra-halftime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The home football team score at halftime in extra time'
            )
            ->addOption(
                name: 'away-team-extra-halftime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The away football team score at halftime in extra time'
            )
            ->addOption(
                name: 'home-team-extra-fulltime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The home football team score at the end of extra time'
            )
            ->addOption(
                name: 'away-team-extra-fulltime-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The away football team score at the end of extra time'
            )
            ->addOption(
                name: 'home-team-shootout-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The home football team shootout score'
            )
            ->addOption(
                name: 'away-team-shootout-score',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The away football team shootout score'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>football match</comment> in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id>
                    [--home-team-id <home-team-id>] [--away-team-id <away-team-id>]
                    [--timestamp <timestamp>] [--round <round>]
                    [--home-team-halftime-score <home-team-halftime-score>] [--away-team-halftime-score <away-team-halftime-score>]
                    [--home-team-fulltime-score <home-team-fulltime-score>] [--away-team-fulltime-score <away-team-fulltime-score>]
                    [--home-team-extra-halftime-score <home-team-extra-halftime-score>] [--away-team-extra-halftime-score <away-team-extra-halftime-score>]
                    [--home-team-extra-fulltime-score <home-team-extra-fulltime-score>] [--away-team-extra-fulltime-score <away-team-extra-fulltime-score>]
                    [--home-team-shootout-score <home-team-shootout-score>] [--away-team-shootout-score <away-team-shootout-score>]</info>

                Examples:
                  <info>%command.full_name% 1 --home-team-id 1 --away-team-id 2 --timestamp 1781204400 --round 1</info>

                If no competition id is specified, you'll be prompted interactively.
                HELP
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->interactFootballCompetition($input, $output, 'competition-id', 'Football competition: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Create Football Match');

        try {
            $competition = $this->parseFootballCompetitionIdArgument($input, 'competition-id');

            $homeTeam = $this->parseFootballTeamIdOption($input, 'home-team-id');
            if ($homeTeam !== null) {
                $count = $this->entityManager
                    ->getRepository(FootballCompetitionTeamEntry::class)
                    ->count(['competition' => $competition, 'team' => $homeTeam]);
                if ($count !== 1) {
                    $io->error('Home football team not entered into competition.');
                    return Command::FAILURE;
                }
            }

            $awayTeam = $this->parseFootballTeamIdOption($input, 'away-team-id');
            if ($awayTeam !== null) {
                $count = $this->entityManager
                    ->getRepository(FootballCompetitionTeamEntry::class)
                    ->count(['competition' => $competition, 'team' => $awayTeam]);
                if ($count !== 1) {
                    $io->error('Away football team not entered into competition.');
                    return Command::FAILURE;
                }
            }

            if ($homeTeam !== null && $awayTeam !== null && $homeTeam->getId() === $awayTeam->getId()) {
                $io->error('Football match must have distinct home and away teams.');
                return Command::FAILURE;
            }

            $match = (new FootballMatch())
                ->setCompetition($competition)
                ->setHomeTeam($homeTeam)
                ->setAwayTeam($awayTeam)
                ->setTimestamp($this->parseIntOption($input, 'timestamp'))
                ->setRound($this->parseIntOption($input, 'round'))
                ->setHomeTeamHalftimeScore($this->parseIntOption($input, 'home-team-halftime-score'))
                ->setAwayTeamHalftimeScore($this->parseIntOption($input, 'away-team-halftime-score'))
                ->setHomeTeamFulltimeScore($this->parseIntOption($input, 'home-team-fulltime-score'))
                ->setAwayTeamFulltimeScore($this->parseIntOption($input, 'away-team-fulltime-score'))
                ->setHomeTeamExtraHalftimeScore($this->parseIntOption($input, 'home-team-extra-halftime-score'))
                ->setAwayTeamExtraHalftimeScore($this->parseIntOption($input, 'away-team-extra-halftime-score'))
                ->setHomeTeamExtraFulltimeScore($this->parseIntOption($input, 'home-team-extra-fulltime-score'))
                ->setAwayTeamExtraFulltimeScore($this->parseIntOption($input, 'away-team-extra-fulltime-score'))
                ->setHomeTeamShootoutScore($this->parseIntOption($input, 'home-team-shootout-score'))
                ->setAwayTeamShootoutScore($this->parseIntOption($input, 'away-team-shootout-score'));

            $this->validate($match);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $match,
                    [
                        AbstractNormalizer::GROUPS => [
                            FootballMatch::GROUP_DETAIL,
                            FootballCompetition::GROUP_LIST,
                            FootballTeam::GROUP_LIST
                        ]
                    ]
                ));

                if (!$io->confirm('Create football match?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($match);
            $this->entityManager->flush();

            $io->success(sprintf('Football match has been created with id %d.', $match->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

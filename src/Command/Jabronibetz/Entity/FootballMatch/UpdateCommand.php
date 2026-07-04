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
    name: 'app:jabronibetz:entity:football-match:update',
    description: 'Update a football match'
)]
final class UpdateCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football match'
            )
            ->addOption(
                name: 'competition-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football competition for the match'
            )
            ->addOption(
                name: 'home-team-id',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The id of the home football team in the match',
                default: false
            )
            ->addOption(
                name: 'away-team-id',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The id of the away football team in the match',
                default: false
            )
            ->addOption(
                name: 'timestamp',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The timestamp for the football match',
                default: false
            )
            ->addOption(
                name: 'round',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The round of the football match',
                default: false
            )
            ->addOption(
                name: 'home-team-halftime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The home football team score at halftime',
                default: false
            )
            ->addOption(
                name: 'away-team-halftime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The away football team score at halftime',
                default: false
            )
            ->addOption(
                name: 'home-team-fulltime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The home football team score at fulltime',
                default: false
            )
            ->addOption(
                name: 'away-team-fulltime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The away football team score at fulltime',
                default: false
            )
            ->addOption(
                name: 'home-team-extra-halftime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The home football team score at halftime in extra time',
                default: false
            )
            ->addOption(
                name: 'away-team-extra-halftime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The away football team score at halftime in extra time',
                default: false
            )
            ->addOption(
                name: 'home-team-extra-fulltime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The home football team score at the end of extra time',
                default: false
            )
            ->addOption(
                name: 'away-team-extra-fulltime-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The away football team score at the end of extra time',
                default: false
            )
            ->addOption(
                name: 'home-team-shootout-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The home football team shootout score',
                default: false
            )
            ->addOption(
                name: 'away-team-shootout-score',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The away football team shootout score',
                default: false
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a
                <comment>football match</comment> in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id>
                    [--competition-id <competition-id>]
                    [--home-team-id [<home-team-id>]] [--away-team-id [<away-team-id>]]
                    [--timestamp [<timestamp>]] [--round [<round>]]
                    [--home-team-halftime-score [<home-team-halftime-score>]] [--away-team-halftime-score [<away-team-halftime-score>]]
                    [--home-team-fulltime-score [<home-team-fulltime-score>]] [--away-team-fulltime-score [<away-team-fulltime-score>]]
                    [--home-team-extra-halftime-score [<home-team-extra-halftime-score>]] [--away-team-extra-halftime-score [<away-team-extra-halftime-score>]]
                    [--home-team-extra-fulltime-score [<home-team-extra-fulltime-score>]] [--away-team-extra-fulltime-score [<away-team-extra-fulltime-score>]]
                    [--home-team-shootout-score [<home-team-shootout-score>]] [--away-team-shootout-score [<away-team-shootout-score>]]</info>

                Examples:
                  <info>%command.full_name% 1 --result Winners</info>

                If no id is specified, you'll be prompted interactively.
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
        $this->interactFootballMatch($input, $output, 'id', 'Football match: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Update Football Match');

        try {
            $match = $this->parseFootballMatchArgument($input, 'id');

            $match->setCompetition(
                $this->parseFootballCompetitionOption($input, 'competition-id') ?? $match->getCompetition()
            );

            $homeTeam = $this->parseFootballTeamOption($input, 'home-team-id');
            $homeTeam = $homeTeam === false ? $match->getHomeTeam() : $homeTeam;
            if ($homeTeam !== null) {
                $count = $this->entityManager
                    ->getRepository(FootballCompetitionTeamEntry::class)
                    ->count(['competition' => $match->getCompetition(), 'team' => $homeTeam]);
                if ($count !== 1) {
                    $io->error('Home football team not entered into competition.');
                    return Command::FAILURE;
                }
            }

            $awayTeam = $this->parseFootballTeamOption($input, 'away-team-id');
            $awayTeam = $awayTeam === false ? $match->getAwayTeam() : $awayTeam;
            if ($awayTeam !== null) {
                $count = $this->entityManager
                    ->getRepository(FootballCompetitionTeamEntry::class)
                    ->count(['competition' => $match->getCompetition(), 'team' => $awayTeam]);
                if ($count !== 1) {
                    $io->error('Away football team not entered into competition.');
                    return Command::FAILURE;
                }
            }

            if ($homeTeam !== null && $awayTeam !== null && $homeTeam->getId() === $awayTeam->getId()) {
                $io->error('Football match must have distinct home and away teams.');
                return Command::FAILURE;
            }

            $match->setHomeTeam($homeTeam);
            $match->setAwayTeam($awayTeam);

            $timestamp = $this->parseIntOption($input, 'timestamp');
            $match->setTimestamp($timestamp === false ? $match->getTimestamp() : $timestamp);

            $round = $this->parseIntOption($input, 'round');
            $match->setRound($round === false ? $match->getRound() : $round);

            $homeTeamHalftimeScore = $this->parseIntOption($input, 'home-team-halftime-score');
            $match->setHomeTeamHalftimeScore(
                $homeTeamHalftimeScore === false ? $match->getHomeTeamHalftimeScore() : $homeTeamHalftimeScore
            );

            $awayTeamHalftimeScore = $this->parseIntOption($input, 'away-team-halftime-score');
            $match->setAwayTeamHalftimeScore(
                $awayTeamHalftimeScore === false ? $match->getAwayTeamHalftimeScore() : $awayTeamHalftimeScore
            );

            $homeTeamFulltimeScore = $this->parseIntOption($input, 'home-team-fulltime-score');
            $match->setHomeTeamFulltimeScore(
                $homeTeamFulltimeScore === false ? $match->getHomeTeamFulltimeScore() : $homeTeamFulltimeScore
            );

            $awayTeamFulltimeScore = $this->parseIntOption($input, 'away-team-fulltime-score');
            $match->setAwayTeamFulltimeScore(
                $awayTeamFulltimeScore === false ? $match->getAwayTeamFulltimeScore() : $awayTeamFulltimeScore
            );

            $homeTeamExtraHalftimeScore = $this->parseIntOption($input, 'home-team-extra-halftime-score');
            $match->setHomeTeamExtraHalftimeScore(
                $homeTeamExtraHalftimeScore === false
                    ? $match->getHomeTeamExtraHalftimeScore()
                    : $homeTeamExtraHalftimeScore
            );

            $awayTeamExtraHalftimeScore = $this->parseIntOption($input, 'away-team-extra-halftime-score');
            $match->setAwayTeamExtraHalftimeScore(
                $awayTeamExtraHalftimeScore === false
                    ? $match->getAwayTeamExtraHalftimeScore()
                    : $awayTeamExtraHalftimeScore
            );

            $homeTeamExtraFulltimeScore = $this->parseIntOption($input, 'home-team-extra-fulltime-score');
            $match->setHomeTeamExtraFulltimeScore(
                $homeTeamExtraFulltimeScore === false
                    ? $match->getHomeTeamExtraFulltimeScore()
                    : $homeTeamExtraFulltimeScore
            );

            $awayTeamExtraFulltimeScore = $this->parseIntOption($input, 'away-team-extra-fulltime-score');
            $match->setAwayTeamExtraFulltimeScore(
                $awayTeamExtraFulltimeScore === false
                    ? $match->getAwayTeamExtraFulltimeScore()
                    : $awayTeamExtraFulltimeScore
            );

            $homeTeamShootoutScore = $this->parseIntOption($input, 'home-team-shootout-score');
            $match->setHomeTeamShootoutScore(
                $homeTeamShootoutScore === false ? $match->getHomeTeamShootoutScore() : $homeTeamShootoutScore
            );

            $awayTeamShootoutScore = $this->parseIntOption($input, 'away-team-shootout-score');
            $match->setAwayTeamShootoutScore(
                $awayTeamShootoutScore === false ? $match->getAwayTeamShootoutScore() : $awayTeamShootoutScore
            );

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
                if (!$io->confirm('Update football match?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($match);
            $this->entityManager->flush();

            $io->success(sprintf('Football match with id %d has been updated.', $match->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

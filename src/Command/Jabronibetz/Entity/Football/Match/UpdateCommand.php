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

namespace App\Command\Jabronibetz\Entity\Football\Match;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Repository\FootballMatchRepository;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football:match:update',
    description: 'Update a football match',
    aliases: ['app:jbetz:footy:match:update'],
)]
final class UpdateCommand extends Command
{
    /**
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $jabronibetzEntityManager Autowiring alias
     * @param DefinitionListConverter $definitionListConverter
     */
    public function __construct(
        private readonly ValidatorInterface      $validator,
        private readonly EntityManagerInterface  $jabronibetzEntityManager,
        private readonly DefinitionListConverter $definitionListConverter
    )
    {
        parent::__construct();
    }

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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if ($input->getArgument('id') === null) {
            /** @var FootballMatchRepository $repo */
            $repo = $this->jabronibetzEntityManager->getRepository(FootballMatch::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Football match id: ', $choices));
                $input->setArgument('id', array_search($choice, $choices, true));
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Football Match Update');

        try {
            $match = $this->jabronibetzEntityManager->find(FootballMatch::class, $input->getArgument('id'));
            if ($match === null) {
                $io->error('Football match not found');
                return Command::FAILURE;
            }

            $cmpId = $input->getOption('competition-id');
            if ($cmpId !== null) {
                $cmp = $this->jabronibetzEntityManager->find(FootballCompetition::class, $cmpId);
                if ($cmp === null) {
                    $io->error('Football competition not found');
                    return Command::FAILURE;
                }
            } else {
                $cmp = $match->getCompetition();
            }
            $match->setCompetition($cmp);

            $homeTeamId = $input->getOption('home-team-id');
            if ($homeTeamId !== null) {
                $homeTeam = $this->jabronibetzEntityManager->find(FootballTeam::class, $homeTeamId);
                if ($homeTeam === null) {
                    $io->error('Home football team not found');
                    return Command::FAILURE;
                }
            } else {
                $homeTeam = $match->getTeam();
            }
            if ($homeTeam !== null) {
                $count = $this->jabronibetzEntityManager
                    ->getRepository(FootballCompetitionTeamEntry::class)
                    ->count(['competition' => $cmp, 'team' => $homeTeam]);
                if ($count !== 1) {
                    $io->error('Home football team not entered into competition.');
                    return Command::FAILURE;
                }
            }
            $match->setHomeTeam($homeTeam);

            $awayTeamId = $input->getOption('away-team-id');
            if ($awayTeamId !== null) {
                $awayTeam = $this->jabronibetzEntityManager->find(FootballTeam::class, $awayTeamId);
                if ($awayTeam === null) {
                    $io->error('Away football team not found');
                    return Command::FAILURE;
                }
            } else {
                $awayTeam = $match->getTeam();
            }
            if ($awayTeam !== null) {
                $count = $this->jabronibetzEntityManager
                    ->getRepository(FootballCompetitionTeamEntry::class)
                    ->count(['competition' => $cmp, 'team' => $awayTeam]);
                if ($count !== 1) {
                    $io->error('Away football team not entered into competition.');
                    return Command::FAILURE;
                }
            }
            $match->setAwayTeam($awayTeam);

            if ($homeTeam !== null && $awayTeam !== null && $homeTeam->getId() === $awayTeam->getId()) {
                $io->error('Football match must have distinct home and away teams.');
                return Command::FAILURE;
            }

            $timestamp = $input->getOption('timestamp');
            if ($timestamp === false) {
                $timestamp = $match->getTimestamp();
            }
            if ($timestamp !== null) {
                if (!is_numeric($timestamp)) {
                    $io->error('The timestamp option must be a numeric value.');
                    return Command::FAILURE;
                }
                $timestamp = intval($timestamp);
            }
            $match->setTimestamp($timestamp);

            $round = $input->getOption('round');
            if ($round === false) {
                $round = $match->getRound();
            }
            if ($round !== null) {
                if (!is_numeric($round)) {
                    $io->error('The round option must be a numeric value.');
                    return Command::FAILURE;
                }
                $round = intval($round);
            }
            $match->setRound($round);

            $homeTeamHalftimeScore = $input->getOption('home-team-halftime-score');
            if ($homeTeamHalftimeScore === false) {
                $homeTeamHalftimeScore = $match->getHomeTeamHalftimeScore();
            }
            if ($homeTeamHalftimeScore !== null) {
                if (!is_numeric($homeTeamHalftimeScore)) {
                    $io->error('The home-team-halftime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $homeTeamHalftimeScore = intval($homeTeamHalftimeScore);
            }
            $match->setHomeTeamHalftimeScore($homeTeamHalftimeScore);

            $awayTeamHalftimeScore = $input->getOption('away-team-halftime-score');
            if ($awayTeamHalftimeScore === false) {
                $awayTeamHalftimeScore = $match->getAwayTeamHalftimeScore();
            }
            if ($awayTeamHalftimeScore !== null) {
                if (!is_numeric($awayTeamHalftimeScore)) {
                    $io->error('The away-team-halftime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $awayTeamHalftimeScore = intval($awayTeamHalftimeScore);
            }
            $match->setAwayTeamHalftimeScore($awayTeamHalftimeScore);

            $homeTeamFulltimeScore = $input->getOption('home-team-fulltime-score');
            if ($homeTeamFulltimeScore === false) {
                $homeTeamFulltimeScore = $match->getHomeTeamFulltimeScore();
            }
            if ($homeTeamFulltimeScore !== null) {
                if (!is_numeric($homeTeamFulltimeScore)) {
                    $io->error('The home-team-fulltime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $homeTeamFulltimeScore = intval($homeTeamFulltimeScore);
            }
            $match->setHomeTeamFulltimeScore($homeTeamFulltimeScore);

            $awayTeamFulltimeScore = $input->getOption('away-team-fulltime-score');
            if ($awayTeamFulltimeScore === false) {
                $awayTeamFulltimeScore = $match->getAwayTeamFulltimeScore();
            }
            if ($awayTeamFulltimeScore !== null) {
                if (!is_numeric($awayTeamFulltimeScore)) {
                    $io->error('The away-team-fulltime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $awayTeamFulltimeScore = intval($awayTeamFulltimeScore);
            }
            $match->setAwayTeamFulltimeScore($awayTeamFulltimeScore);

            $homeTeamExtraHalftimeScore = $input->getOption('home-team-extra-halftime-score');
            if ($homeTeamExtraHalftimeScore === false) {
                $homeTeamExtraHalftimeScore = $match->getHomeTeamExtraHalftimeScore();
            }
            if ($homeTeamExtraHalftimeScore !== null) {
                if (!is_numeric($homeTeamExtraHalftimeScore)) {
                    $io->error('The home-team-extra-halftime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $homeTeamExtraHalftimeScore = intval($homeTeamExtraHalftimeScore);
            }
            $match->setHomeTeamExtraHalftimeScore($homeTeamExtraHalftimeScore);

            $awayTeamExtraHalftimeScore = $input->getOption('away-team-extra-halftime-score');
            if ($awayTeamExtraHalftimeScore === false) {
                $awayTeamExtraHalftimeScore = $match->getAwayTeamExtraHalftimeScore();
            }
            if ($awayTeamExtraHalftimeScore !== null) {
                if (!is_numeric($awayTeamExtraHalftimeScore)) {
                    $io->error('The away-team-extra-halftime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $awayTeamExtraHalftimeScore = intval($awayTeamExtraHalftimeScore);
            }
            $match->setAwayTeamExtraHalftimeScore($awayTeamExtraHalftimeScore);

            $homeTeamExtraFulltimeScore = $input->getOption('home-team-extra-fulltime-score');
            if ($homeTeamExtraFulltimeScore === false) {
                $homeTeamExtraFulltimeScore = $match->getHomeTeamExtraFulltimeScore();
            }
            if ($homeTeamExtraFulltimeScore !== null) {
                if (!is_numeric($homeTeamExtraFulltimeScore)) {
                    $io->error('The home-team-extra-fulltime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $homeTeamExtraFulltimeScore = intval($homeTeamExtraFulltimeScore);
            }
            $match->setHomeTeamExtraFulltimeScore($homeTeamExtraFulltimeScore);

            $awayTeamExtraFulltimeScore = $input->getOption('away-team-extra-fulltime-score');
            if ($awayTeamExtraFulltimeScore === false) {
                $awayTeamExtraFulltimeScore = $match->getAwayTeamExtraFulltimeScore();
            }
            if ($awayTeamExtraFulltimeScore !== null) {
                if (!is_numeric($awayTeamExtraFulltimeScore)) {
                    $io->error('The away-team-extra-fulltime-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $awayTeamExtraFulltimeScore = intval($awayTeamExtraFulltimeScore);
            }
            $match->setAwayTeamExtraFulltimeScore($awayTeamExtraFulltimeScore);

            $homeTeamShootoutScore = $input->getOption('home-team-shootout-score');
            if ($homeTeamShootoutScore === false) {
                $homeTeamShootoutScore = $match->getHomeTeamShootoutScore();
            }
            if ($homeTeamShootoutScore !== null) {
                if (!is_numeric($homeTeamShootoutScore)) {
                    $io->error('The home-team-shootout-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $homeTeamShootoutScore = intval($homeTeamShootoutScore);
            }
            $match->setHomeTeamShootoutScore($homeTeamShootoutScore);

            $awayTeamShootoutScore = $input->getOption('away-team-shootout-score');
            if ($awayTeamShootoutScore === false) {
                $awayTeamShootoutScore = $match->getAwayTeamShootoutScore();
            }
            if ($awayTeamShootoutScore !== null) {
                if (!is_numeric($awayTeamShootoutScore)) {
                    $io->error('The away-team-shootout-score option must be a numeric value.');
                    return Command::FAILURE;
                }
                $awayTeamShootoutScore = intval($awayTeamShootoutScore);
            }
            $match->setAwayTeamShootoutScore($awayTeamShootoutScore);

            $errors = $this->validator->validate($match);
            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
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

            $this->jabronibetzEntityManager->persist($match);
            $this->jabronibetzEntityManager->flush();

            $io->success(sprintf(
                'Football match %s with id %d has been updated.',
                $match->getChoiceValue(),
                $match->getId()
            ));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

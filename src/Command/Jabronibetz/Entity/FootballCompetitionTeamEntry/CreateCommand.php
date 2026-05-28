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

namespace App\Command\Jabronibetz\Entity\FootballCompetitionTeamEntry;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Repository\FootballCompetitionRepository;
use App\Domain\Jabronibetz\Repository\FootballTeamRepository;
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
    name: 'app:jabronibetz:entity:football-competition-team-entry:create',
    description: 'Create a football competition team entry'
)]
final class CreateCommand extends Command
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
                name: 'competition-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football competition that a team is entering'
            )
            ->addArgument(
                name: 'team-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football team entering the competition'
            )
            ->addOption(
                name: 'group',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The group in which the football team begins the competition'
            )
            ->addOption(
                name: 'result',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The result of the football team in the competition'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>football competition team entry</comment> in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id> <team-id> [--group [<group>]] [--result [<result>]]</info>

                Examples:
                  <info>%command.full_name% 1 1 --group A</info>

                If no competition id or team id is specified, you'll be prompted interactively.
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

        if ($input->getArgument('competition-id') === null) {
            /** @var FootballCompetitionRepository $repo */
            $repo = $this->jabronibetzEntityManager->getRepository(FootballCompetition::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Football competition id: ', $choices));
                $input->setArgument('competition-id', array_search($choice, $choices, true));
            }
        }

        if ($input->getArgument('team-id') === null) {
            /** @var FootballTeamRepository $repo */
            $repo = $this->jabronibetzEntityManager->getRepository(FootballTeam::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Football team id: ', $choices));
                $input->setArgument('team-id', array_search($choice, $choices, true));
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
        $io->title('Jabronibetz: Create Football Competition Team Entry');

        try {
            $cmp = $this->jabronibetzEntityManager->find(FootballCompetition::class, $input->getArgument('competition-id'));
            if ($cmp === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $team = $this->jabronibetzEntityManager->find(FootballTeam::class, $input->getArgument('team-id'));
            if ($team === null) {
                $io->error('Football team not found');
                return Command::FAILURE;
            }

            $group = $input->getOption('group');
            if ($group !== null) {
                $group = trim($group);
            }

            $result = $input->getOption('result');
            if ($result !== null) {
                $result = trim($result);
            }

            $entry = (new FootballCompetitionTeamEntry())
                ->setCompetition($cmp)
                ->setTeam($team)
                ->setGroup($group)
                ->setResult($result);

            $errors = $this->validator->validate($entry);
            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $entry,
                    [
                        AbstractNormalizer::GROUPS => [
                            FootballCompetitionTeamEntry::GROUP_DETAIL,
                            FootballCompetition::GROUP_LIST,
                            FootballTeam::GROUP_LIST
                        ]
                    ]
                ));

                if (!$io->confirm('Create football competition team entry?')) {
                    return Command::SUCCESS;
                }
            }

            $this->jabronibetzEntityManager->persist($entry);
            $this->jabronibetzEntityManager->flush();

            $io->success(sprintf(
                'Football competition team entry %s has been created with id %d.',
                $entry->getChoiceValue(),
                $entry->getId()
            ));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

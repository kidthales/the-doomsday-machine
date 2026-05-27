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

namespace App\Command\Jabronibetz\Football\CompetitionTeamEntry;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Repository\FootballCompetitionTeamEntryRepository;
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
    name: 'app:jabronibetz:football:competition-team-entry:update',
    description: 'Update a football competition team entry',
    aliases: ['app:jbetz:footy:cmp-team-entry:update'],
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
                description: 'The id of the football competition team entry'
            )
            ->addOption(
                name: 'competition-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football competition that a team entered'
            )
            ->addOption(
                name: 'team-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football team that entered the competition'
            )
            ->addOption(
                name: 'group',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The group in which the football team began the competition',
                default: false
            )
            ->addOption(
                name: 'result',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The result of the football team in the competition',
                default: false
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a
                <comment>football competition team entry</comment> in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--competition-id <competition-id>] [--team-id <team-id>] [--group [<group>] [--result [<result>]]</info>

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
            /** @var FootballCompetitionTeamEntryRepository $repo */
            $repo = $this->jabronibetzEntityManager->getRepository(FootballCompetitionTeamEntry::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Football competition team entry id: ', $choices));
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
        $io->title('Jabronibetz: Football Competition Team Entry Update');

        try {
            $entry = $this->jabronibetzEntityManager->find(FootballCompetitionTeamEntry::class, $input->getArgument('id'));
            if ($entry === null) {
                $io->error('Football competition team entry not found');
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
                $cmp = $entry->getCompetition();
            }
            $entry->setCompetition($cmp);

            $teamId = $input->getOption('team-id');
            if ($teamId !== null) {
                $team = $this->jabronibetzEntityManager->find(FootballTeam::class, $teamId);
                if ($team === null) {
                    $io->error('Football team not found');
                    return Command::FAILURE;
                }
            } else {
                $team = $entry->getTeam();
            }
            $entry->setTeam($team);

            $group = $input->getOption('group');
            if ($group === false) {
                $group = $entry->getGroup();
            }
            if ($group !== null) {
                $group = trim($group);
            }
            $entry->setGroup($group);

            $result = $input->getOption('result');
            if ($result === false) {
                $result = $entry->getResult();
            }
            if ($result !== null) {
                $result = trim($result);
            }
            $entry->setResult($result);

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

                if (!$io->confirm('Update football competition team entry?')) {
                    return Command::SUCCESS;
                }
            }

            $this->jabronibetzEntityManager->persist($entry);
            $this->jabronibetzEntityManager->flush();

            $io->success(sprintf(
                'Football competition team entry %s with id %d has been updated.',
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

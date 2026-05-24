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

namespace App\Command\Jabronibetz\Football\Team;

use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Entity\FootballOrganization;
use App\Domain\Jabronibetz\Enum\FootballGender;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football:team:update',
    description: 'Update a football team',
    aliases: ['app:jbetz:footy:team:update'],
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
        $availableGenders = '<info>' . implode('</info>, <info>', array_column(FootballGender::cases(), 'value')) . '</info>';

        $this
            ->addArgument(
                name: 'id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football team'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the football team'
            )
            ->addOption(
                name: 'short-name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The short name of the football team'
            )
            ->addOption(
                name: 'organization-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football organization managing this team'
            )
            ->addOption(
                name: 'gender',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The gender of the football team'
            )
            ->setHelp(
                <<<HELP
                The <info>%command.name%</info> command allows you to update a <comment>football team</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--name <name>] [--short-name <short-name>] [--organization-id <organization-id>] [--gender <gender>]</info>

                Examples:
                  <info>%command.full_name% 1 --short-name THIEFA</info>

                If no id is specified, you'll be prompted interactively.
                Gender may be one of $availableGenders.
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
            $input->setArgument('id', $helper->ask($input, $output, new Question('Football team id: ')));
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
        $io->title('Jabronibetz: Football Team Update');

        try {
            $team = $this->jabronibetzEntityManager->find(FootballTeam::class, $input->getArgument('id'));

            if ($team === null) {
                $io->error('Football team not found');
                return Command::FAILURE;
            }

            $team->setName(trim($input->getOption('name') ?? $team->getName()));
            $team->setShortName(trim($input->getOption('short-name') ?? $team->getShortName()));

            $orgId = $input->getOption('organization-id');
            if ($orgId !== null) {
                $org = $this->jabronibetzEntityManager->find(FootballOrganization::class, $orgId);

                if ($org === null) {
                    $io->error('Football organization not found');
                    return Command::FAILURE;
                }
            } else {
                $org = $team->getManagingOrganization();
            }
            $team->setManagingOrganization($org);

            $gender = $input->getOption('gender');
            if ($gender !== null) {
                $team->setGender(FootballGender::from($gender));
            }

            $errors = $this->validator->validate($team);

            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $team,
                    [
                        AbstractNormalizer::GROUPS => FootballTeam::GROUP_DETAIL
                    ]
                ));

                if (!$io->confirm('Update football team?')) {
                    return Command::SUCCESS;
                }
            }

            $this->jabronibetzEntityManager->persist($team);
            $this->jabronibetzEntityManager->flush();

            $io->success(sprintf('Football team with id %d has been updated.', $team->getId()));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

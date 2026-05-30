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

namespace App\Command\Jabronibetz\Entity\FootballTeam;

use App\Domain\Jabronibetz\Entity\FootballOrganization;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Enum\FootballGender;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
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
    name: 'app:jabronibetz:entity:football-team:update',
    description: 'Update a football team'
)]
final class UpdateCommand extends Command
{
    use EntityManagerAwareTrait;

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
        $this->interactChoiceQuestionWithChoosables(
            $input,
            $output,
            'id',
            'Football team id: ',
            $this->entityManager->getRepository(FootballTeam::class)->findAll(),
            true
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Update Football Team');

        try {
            $team = $this->entityManager->find(FootballTeam::class, $input->getArgument('id'));
            if ($team === null) {
                $io->error('Football team not found');
                return Command::FAILURE;
            }

            $team->setName(trim($input->getOption('name') ?? $team->getName()));
            $team->setShortName(trim($input->getOption('short-name') ?? $team->getShortName()));

            $orgId = $input->getOption('organization-id');
            if ($orgId !== null) {
                $org = $this->entityManager->find(FootballOrganization::class, $orgId);
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

            $this->entityManager->persist($team);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Football team %s with id %d has been updated.',
                $team->getChoiceValue(),
                $team->getId()
            ));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

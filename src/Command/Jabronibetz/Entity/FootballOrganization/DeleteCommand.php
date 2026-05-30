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

namespace App\Command\Jabronibetz\Entity\FootballOrganization;

use App\Domain\Jabronibetz\Entity\FootballOrganization;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:entity:football-organization:delete',
    description: 'Delete a football organization'
)]
final class DeleteCommand extends Command
{
    use EntityManagerAwareTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football organization'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to delete a <comment>football organization</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id></info>

                Examples:
                  <info>%command.full_name% 1</info>

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
        $this->interactChoiceQuestionWithChoosables(
            $input,
            $output,
            'id',
            'Football organization id: ',
            $this->entityManager->getRepository(FootballOrganization::class)->findAll(),
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
        $io->title('Jabronibetz: Delete Football Organization');

        try {
            $org = $this->entityManager->find(FootballOrganization::class, $input->getArgument('id'));
            if ($org === null) {
                $io->error('Football organization not found');
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $org,
                    [
                        AbstractNormalizer::GROUPS => FootballOrganization::GROUP_DETAIL
                    ]
                ));

                $numCmps = $org->getManagedCompetitions()->count();
                if ($numCmps > 0) {
                    $io->warning(sprintf('%d football competitions will also be deleted!', $numCmps));
                }

                $numTeams = $org->getManagedTeams()->count();
                if ($numTeams > 0) {
                    $io->warning(sprintf('%d football teams will also be deleted!', $numTeams));
                }

                if (!$io->confirm('Delete football organization?')) {
                    return Command::SUCCESS;
                }
            }

            $id = $org->getId();

            $this->entityManager->remove($org);
            $this->entityManager->flush();

            $io->success(sprintf('Football organization %s with id %d has been deleted.', $org->getChoiceValue(), $id));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

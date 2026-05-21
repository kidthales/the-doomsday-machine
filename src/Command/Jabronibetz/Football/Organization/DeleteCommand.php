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

namespace App\Command\Jabronibetz\Football\Organization;

use App\Domain\Jabronibetz\Entity\FootballOrganization;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football:organization:delete',
    description: 'Delete a football organization',
    aliases: ['app:jbetz:footy:org:delete'],
)]
final class DeleteCommand extends Command
{
    /**
     * @param EntityManagerInterface $jabronibetzEntityManager Autowiring alias
     */
    public function __construct(
        private readonly EntityManagerInterface  $jabronibetzEntityManager,
        private readonly DefinitionListConverter $definitionListConverter,
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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if ($input->getArgument('id') === null) {
            $input->setArgument('id', $helper->ask($input, $output, new Question('Football organization id: ')));
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
        $io->title('Jabronibetz: Football Organization Delete');

        try {
            $org = $this->jabronibetzEntityManager->find(FootballOrganization::class, $input->getArgument('id'));

            if ($org === null) {
                $io->error('Football organization not found');
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $org,
                    [
                        AbstractNormalizer::GROUPS => FootballOrganization::GROUP_DELETE
                    ]
                ));

                $io->warning(
                    sprintf('%d football competitions will also be deleted!', $org->getCompetitions()->count())
                );

                if (!$io->confirm('Delete football organization?')) {
                    return Command::SUCCESS;
                }
            }

            $id = $org->getId();

            $this->jabronibetzEntityManager->remove($org);
            $this->jabronibetzEntityManager->flush();

            $io->success(sprintf(
                'Football organization %s (%s) with id %d has been deleted.',
                $org->getName(),
                $org->getShortName(),
                $id
            ));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

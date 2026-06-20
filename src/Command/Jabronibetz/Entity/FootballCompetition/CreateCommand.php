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

namespace App\Command\Jabronibetz\Entity\FootballCompetition;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballOrganization;
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
    name: 'app:jabronibetz:entity:football-competition:create',
    description: 'Create a football competition'
)]
final class CreateCommand extends Command
{
    use EntityManagerAwareTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the football competition'
            )
            ->addArgument(
                name: 'short-name',
                mode: InputArgument::REQUIRED,
                description: 'The short name of the football competition'
            )
            ->addArgument(
                name: 'organization-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football organization that manages the competition'
            )
            ->addOption(
                name: 'rounds',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The total football match rounds for the competition'
            )
            ->addOption(
                name: 'group-rounds',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The total football match rounds for the competition\'s group phase'
            )
            ->addOption(
                name: 'separate-match-xg-home-away',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Flag if separate football match xg calculations are used for home and away teams'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a <comment>football competition</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <name> <short-name> <organization-id>
                    [--rounds [<rounds>]] [--group-rounds [<group-rounds>]]
                    [--separate-match-xg-home-away [<separate-match-xg-home-away>]]</info>

                Examples:
                  <info>%command.full_name% "2026 FIFA World Cup" FWC26 1 --rounds 9 --group-rounds 3 --separate-match-xg-home-away false</info>

                If no name, short name, or organization id is specified, you'll be prompted interactively.
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
        $this->interactQuestion($input, $output, 'name', 'Football competition name: ');
        $this->interactQuestion($input, $output, 'short-name', 'Football competition short name: ');
        $this->interactChoiceQuestionWithChoosables(
            $input,
            $output,
            'organization-id',
            'Football competition managed by: ',
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
        $io->title('Jabronibetz: Create Football Competition');

        try {
            $org = $this->entityManager->find(FootballOrganization::class, $input->getArgument('organization-id'));
            if ($org === null) {
                $io->error('Football organization not found');
                return Command::FAILURE;
            }

            $rounds = $input->getOption('rounds');
            if ($rounds !== null) {
                if (!is_numeric($rounds)) {
                    $io->error('The rounds option must be a numeric value.');
                    return Command::FAILURE;
                }
                $rounds = intval($rounds);
            }

            $groupRounds = $input->getOption('group-rounds');
            if ($groupRounds !== null) {
                if (!is_numeric($groupRounds)) {
                    $io->error('The group-rounds option must be a numeric value.');
                    return Command::FAILURE;
                }
                $groupRounds = intval($groupRounds);
            }

            $separateMatchXGHomeAway = $input->getOption('separate-match-xg-home-away');
            if ($separateMatchXGHomeAway !== null) {
                $separateMatchXGHomeAway = filter_var($separateMatchXGHomeAway, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($separateMatchXGHomeAway === null) {
                    $io->error('The separate-match-xg-home-away option must be a boolean value.');
                    return Command::FAILURE;
                }
            }

            $cmp = (new FootballCompetition())
                ->setName(trim($input->getArgument('name')))
                ->setShortName(trim($input->getArgument('short-name')))
                ->setManagingOrganization($org)
                ->setRounds($rounds)
                ->setGroupRounds($groupRounds)
                ->setSeparateMatchXgHomeAway($separateMatchXGHomeAway);

            $errors = $this->validator->validate($cmp);
            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $cmp,
                    [
                        AbstractNormalizer::GROUPS => FootballCompetition::GROUP_DETAIL
                    ]
                ));

                if (!$io->confirm('Create football competition?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($cmp);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Football competition %s has been created with id %d.',
                $cmp->getChoiceValue(),
                $cmp->getId()
            ));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

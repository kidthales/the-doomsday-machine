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
    name: 'app:jabronibetz:entity:football-competition:update',
    description: 'Update a football competition'
)]
final class UpdateCommand extends Command
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
                description: 'The id of the football competition'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the football competition'
            )
            ->addOption(
                name: 'short-name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The short name of the football competition'
            )
            ->addOption(
                name: 'organization-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football organization managing this competition'
            )
            ->addOption(
                name: 'rounds',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The total football match rounds for this competition',
                default: false
            )
            ->addOption(
                name: 'group-rounds',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The total football match rounds for this competition\'s group phase',
                default: false
            )
            ->addOption(
                name: 'separate-match-xg-home-away',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Flag if separate football match xg calculations are used for home and away teams',
                default: '_'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a <comment>football competition</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id>
                    [--name <name>] [--short-name <short-name>] [--organization-id <organization-id>]
                    [--rounds [<rounds>]] [--group-rounds [<group-rounds>]]
                    [--separate-match-xg-home-away [<separate-match-xg-home-away>]]</info>

                Examples:
                  <info>%command.full_name% 1 --short-name THIEFA</info>

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
            'Football competition id: ',
            $this->entityManager->getRepository(FootballCompetition::class)->findAll(),
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
        $io->title('Jabronibetz: Update Football Competition');

        try {
            $cmp = $this->entityManager->find(FootballCompetition::class, $input->getArgument('id'));
            if ($cmp === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $cmp->setName(trim($input->getOption('name') ?? $cmp->getName()));
            $cmp->setShortName(trim($input->getOption('short-name') ?? $cmp->getShortName()));

            $orgId = $input->getOption('organization-id');
            if ($orgId !== null) {
                $org = $this->entityManager->find(FootballOrganization::class, $orgId);
                if ($org === null) {
                    $io->error('Football organization not found');
                    return Command::FAILURE;
                }
            } else {
                $org = $cmp->getManagingOrganization();
            }
            $cmp->setManagingOrganization($org);

            $rounds = $input->getOption('rounds');
            if ($rounds === false) {
                $rounds = $cmp->getRounds();
            }
            if ($rounds !== null) {
                if (!is_numeric($rounds)) {
                    $io->error('The rounds option must be a numeric value.');
                    return Command::FAILURE;
                }
                $rounds = intval($rounds);
            }
            $cmp->setRounds($rounds);

            $groupRounds = $input->getOption('group-rounds');
            if ($groupRounds === false) {
                $groupRounds = $cmp->getGroupRounds();
            }
            if ($groupRounds !== null) {
                if (!is_numeric($groupRounds)) {
                    $io->error('The group-rounds option must be a numeric value.');
                    return Command::FAILURE;
                }
                $groupRounds = intval($groupRounds);
            }
            $cmp->setGroupRounds($groupRounds);

            $separateMatchXGHomeAway = $input->getOption('separate-match-xg-home-away');
            if ($separateMatchXGHomeAway === '_') {
                $separateMatchXGHomeAway = $cmp->getSeparateMatchXgHomeAway();
            }
            if ($separateMatchXGHomeAway !== null) {
                $separateMatchXGHomeAway = filter_var($separateMatchXGHomeAway, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($separateMatchXGHomeAway === null) {
                    $io->error('The separate-match-xg-home-away option must be a boolean value.');
                    return Command::FAILURE;
                }
            }
            $cmp->setSeparateMatchXgHomeAway($separateMatchXGHomeAway);

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

                if (!$io->confirm('Update football competition?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($cmp);
            $this->entityManager->flush();

            $io->success(sprintf(
                'Football competition %s with id %d has been updated.',
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

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

namespace App\Command\BFRPG\Entity\RulesWeaponCategory;

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use App\Domain\BFRPG\ORM\EntityManagerAwareTrait;
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
    name: 'app:bfrpg:entity:rules-weapon-category:update',
    description: 'Update a rules weapon category'
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
                description: 'The id of the rules weapon category'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the rules weapon category'
            )
            ->addOption(
                name: 'source-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules source id for the weapon category'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a
                <comment>rules weapon category</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--name <name>] [--source-id <source-id>]</info>

                Examples:
                  <info>%command.full_name% 1 --name Axes </info>

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
            'Rules weapon category: ',
            $this->entityManager->getRepository(RulesWeaponCategory::class)->findAll(),
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
        $io->title('BFRPG: Update Rules Weapon Category');

        try {
            $weaponCategory = $this->entityManager->find(RulesWeaponCategory::class, $input->getArgument('id'));
            if ($weaponCategory === null) {
                $io->error('Rules weapon category not found');
                return Command::FAILURE;
            }

            $weaponCategory->setName(trim($input->getOption('name') ?? $weaponCategory->getName()));

            $source = null;
            $sourceId = $input->getOption('source-id');
            if ($sourceId !== null) {
                $source = $this->entityManager->find(RulesSource::class, $sourceId);
                if ($source === null) {
                    $io->error('Rules source not found');
                    return Command::FAILURE;
                }
            }
            $weaponCategory->setSource($source ?? $weaponCategory->getSource());

            $errors = $this->validator->validate($weaponCategory);
            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weaponCategory,
                    [
                        AbstractNormalizer::GROUPS => [RulesWeaponCategory::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Update rules weapon category?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weaponCategory);
            $this->entityManager->flush();

            $io->success(
                sprintf(
                    'Rules weapon category %s with id %d has been updated.',
                    $weaponCategory->getChoiceValue(),
                    $weaponCategory->getId()
                )
            );
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

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

use App\Domain\BFRPG\Console\Command\Command;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
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
    name: 'app:bfrpg:entity:rules-weapon-category:create',
    description: 'Create a rules weapon category'
)]
final class CreateCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the rules weapon category'
            )
            ->addArgument(
                name: 'source-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules source id for the weapon category'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>rules weapon category</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <name> <source-id></info>

                Examples:
                  <info>%command.full_name% Axes 1 </info>

                If no name or source id is specified, you'll be prompted interactively.
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
        $this->interactQuestion($input, $output, 'name', 'Rules weapon category name: ');
        $this->interactRulesSource($input, $output, 'source-id', 'Rules weapon category sourced from: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Create Rules Weapon Category');

        try {
            $weaponCategory = (new RulesWeaponCategory())
                ->setName($this->parseStringArgument($input, 'name', true))
                ->setSource($this->parseRulesSourceArgument($input, 'source-id'));

            $this->validate($weaponCategory);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weaponCategory,
                    [
                        AbstractNormalizer::GROUPS => [RulesWeaponCategory::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Create rules weapon category?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weaponCategory);
            $this->entityManager->flush();

            $io->success(sprintf('Rules weapon category has been created with id %d.', $weaponCategory->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

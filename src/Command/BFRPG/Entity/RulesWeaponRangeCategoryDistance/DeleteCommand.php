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

namespace App\Command\BFRPG\Entity\RulesWeaponRangeCategoryDistance;

use App\Domain\BFRPG\Console\Command\Command;
use App\Domain\BFRPG\Entity\RulesWeapon;
use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponRangeCategoryDistance;
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
    name: 'app:bfrpg:entity:rules-weapon-range-category-distance:delete',
    description: 'Delete a rules weapon range category distance'
)]
final class DeleteCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the rules weapon range category distance'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to delete a
                <comment>rules weapon range category distance</comment> in the <comment>BFRPG</comment> db.

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
        $this->interactRulesWeaponRangeCategoryDistance($input, $output, 'id', 'Rules weapon range category distance: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Delete Rules Weapon Range Category Distance');

        try {
            $weaponRangeCategoryDistance = $this->parseRulesWeaponRangeCategoryDistanceIdArgument($input, 'id');

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weaponRangeCategoryDistance,
                    [
                        AbstractNormalizer::GROUPS => [
                            RulesWeaponRangeCategoryDistance::GROUP_DETAIL,
                            RulesWeapon::GROUP_LIST,
                            RulesRangeCategory::GROUP_LIST,
                            RulesSource::GROUP_LIST
                        ]
                    ]
                ));

                if (!$io->confirm('Delete rules weapon range category distance?')) {
                    return Command::SUCCESS;
                }
            }

            $id = $weaponRangeCategoryDistance->getId();

            $this->entityManager->remove($weaponRangeCategoryDistance);
            $this->entityManager->flush();

            $io->success(sprintf('Rules weapon range category distance with id %d has been deleted.', $id));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

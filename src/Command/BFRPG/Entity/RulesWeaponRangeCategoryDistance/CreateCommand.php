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
use App\Domain\BFRPG\Entity\RulesWeaponRangeCategoryDistance;
use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
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
    name: 'app:bfrpg:entity:rules-weapon-range-category-distance:create',
    description: 'Create a rules weapon range category distance'
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
                name: 'weapon-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules weapon id for the weapon range category distance'
            )
            ->addArgument(
                name: 'range-category-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules range category id for the weapon range category distance'
            )
            ->addArgument(
                name: 'distance',
                mode: InputArgument::REQUIRED,
                description: 'The distance for the rules weapon range category distance'
            )
            ->addArgument(
                name: 'source-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules source id for the weapon range category distance'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>rules weapon range category distance</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <weapon-id> <range-category-id> <distance> <source-id></info>

                Examples:
                  <info>%command.full_name% 1 1 10 1</info>

                If no weapon id, range category id, distance, or source id is specified, you'll be prompted interactively.
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
        $this->interactRulesWeapon($input, $output, 'weapon-id', 'Rules weapon range category distance weapon: ');
        $this->interactRulesRangeCategory(
            $input,
            $output,
            'range-category-id',
            'Rules weapon range category distance range category: '
        );
        $this->interactQuestion($input, $output, 'distance', 'Rules weapon range category distance distance: ');
        $this->interactRulesSource($input, $output, 'source-id', 'Rules weapon range category distance sourced from: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Create Rules Weapon Range Category Distance');

        try {
            $weaponRangeCategoryDistance = (new RulesWeaponRangeCategoryDistance())
                ->setWeapon($this->parseRulesWeaponIdArgument($input, 'weapon-id'))
                ->setRangeCategory($this->parseRulesRangeCategoryIdArgument($input, 'range-category-id'))
                ->setDistance($this->parseIntArgument($input, 'distance'))
                ->setSource($this->parseRulesSourceIdArgument($input, 'source-id'));

            $this->validate($weaponRangeCategoryDistance);

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

                if (!$io->confirm('Create rules weapon range category distance?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weaponRangeCategoryDistance);
            $this->entityManager->flush();

            $io->success(
                sprintf(
                    'Rules weapon range category distance has been created with id %d.',
                    $weaponRangeCategoryDistance->getId()
                )
            );
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

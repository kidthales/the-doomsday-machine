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

namespace App\Command\BFRPG\Entity\RulesWeapon;

use App\Domain\BFRPG\Console\Command\Command;
use App\Domain\BFRPG\Entity\RulesWeapon;
use App\Domain\BFRPG\Entity\RulesSource;
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
    name: 'app:bfrpg:entity:rules-weapon:create',
    description: 'Create a rules weapon'
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
                description: 'The name of the rules weapon'
            )
            ->addArgument(
                name: 'price',
                mode: InputArgument::REQUIRED,
                description: 'The price of the rules weapon'
            )
            ->addArgument(
                name: 'weight',
                mode: InputArgument::REQUIRED,
                description: 'The weight of the rules weapon'
            )
            ->addArgument(
                name: 'weapon-category-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules weapon category id for the weapon'
            )
            ->addArgument(
                name: 'source-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules source id for the weapon'
            )
            ->addOption(
                name: 'description',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The description of the rules weapon'
            )
            ->addOption(
                name: 'weapon-size-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules weapon size id for the weapon'
            )
            ->addOption(
                name: 'damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The damage roll for the rules weapon'
            )
            ->addOption(
                name: 'missile-damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The missile damage roll for the rules weapon'
            )
            ->addOption(
                name: 'one-handed-damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The one-handed damage roll for the rules weapon'
            )
            ->addOption(
                name: 'two-handed-damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The two-handed damage roll for the rules weapon'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a <comment>rules weapon</comment>
                in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <name> <price> <weight> <weapon-category-id> <source-id>
                    [--description <description>] [--weapon-size-id <weapon-size-id>]
                    [--damage-roll <damage-roll>] [--missile-damage-roll <missile-damage-roll>]
                    [--one-handed-damage-roll <one-handed-damage-roll>]
                    [--two-handed-damage-roll <two-handed-damage-roll>]</info>

                Examples:
                  <info>%command.full_name% "Hand Axe" 4 5 1 1 --weapon-size-id 1 --damage-roll 1d6 --missile-damage-roll 1d6 --one-handed-damage-roll 1d6</info>

                If no name, price, weight, category id, or source id is specified, you'll be prompted interactively.
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
        $this->interactQuestion($input, $output, 'name', 'Rules weapon name: ');
        $this->interactQuestion($input, $output, 'price', 'Rules weapon price: ');
        $this->interactQuestion($input, $output, 'weight', 'Rules weapon weight: ');
        $this->interactRulesWeaponCategory($input, $output, 'weapon-category-id', 'Rules weapon category: ');
        $this->interactRulesSource($input, $output, 'source-id', 'Rules weapon sourced from: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Create Rules Weapon');

        try {
            $weapon = (new RulesWeapon())
                ->setName($this->parseStringArgument($input, 'name'))
                ->setPrice($this->parseFloatArgument($input, 'price'))
                ->setWeight($this->parseFloatArgument($input, 'weight'))
                ->setDescription($this->parseStringOption($input, 'description', true))
                ->setWeaponSize($this->parseRulesWeaponSizeIdOption($input, 'weapon-size-id'))
                ->setDamageRoll($this->parseStringOption($input, 'damage-roll', true))
                ->setMissileDamageRoll($this->parseStringOption($input, 'missile-damage-roll', true))
                ->setOneHandedDamageRoll($this->parseStringOption($input, 'one-handed-damage-roll', true))
                ->setTwoHandedDamageRoll($this->parseStringOption($input, 'two-handed-damage-roll', true))
                ->setWeaponCategory($this->parseRulesWeaponCategoryIdArgument($input, 'weapon-category-id'))
                ->setSource($this->parseRulesSourceIdArgument($input, 'source-id'));

            $this->validate($weapon);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weapon,
                    [
                        AbstractNormalizer::GROUPS => [RulesWeapon::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Create rules weapon?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weapon);
            $this->entityManager->flush();

            $io->success(sprintf('Rules weapon has been created with id %d.', $weapon->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

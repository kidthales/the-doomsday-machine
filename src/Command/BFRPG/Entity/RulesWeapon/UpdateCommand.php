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
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
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
    name: 'app:bfrpg:entity:rules-weapon:update',
    description: 'Update a rules weapon'
)]
final class UpdateCommand extends Command
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
                description: 'The id of the rules weapon'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the rules weapon'
            )
            ->addOption(
                name: 'price',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The price of the rules weapon'
            )
            ->addOption(
                name: 'weight',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The weight of the rules weapon'
            )
            ->addOption(
                name: 'weapon-category-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules weapon category id for the weapon'
            )
            ->addOption(
                name: 'source-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules source id for the weapon'
            )
            ->addOption(
                name: 'description',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The description of the rules weapon',
                default: false
            )
            ->addOption(
                name: 'weapon-size-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules weapon size id for the weapon',
                default: false
            )
            ->addOption(
                name: 'damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The damage roll for the rules weapon',
                default: false
            )
            ->addOption(
                name: 'missile-damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The missile damage roll for the rules weapon',
                default: false
            )
            ->addOption(
                name: 'one-handed-damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The one-handed damage roll for the rules weapon',
                default: false
            )
            ->addOption(
                name: 'two-handed-damage-roll',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The two-handed damage roll for the rules weapon',
                default: false
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a <comment>rules weapon</comment>
                in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--name <name>] [--price <price>] [--weight <weight>]
                    [--weapon-category-id <weapon-category-id>] [--source-id <source-id>]
                    [--description [<description>]] [--weapon-size-id [<weapon-size-id>]]
                    [--damage-roll [<damage-roll>]] [--missile-damage-roll [<missile-damage-roll>]]
                    [--one-handed-damage-roll [<one-handed-damage-roll>]]
                    [--two-handed-damage-roll [<two-handed-damage-roll>]]</info>

                Examples:
                  <info>%command.full_name% 1 --price 8</info>

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
        $this->interactRulesWeapon($input, $output, 'id', 'Rules weapon: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Update Rules Weapon');

        try {
            $weapon = $this->parseRulesWeaponIdArgument($input, 'id');

            $weapon->setName($this->parseStringOption($input, 'name', true) ?? $weapon->getName());
            $weapon->setPrice($this->parseFloatOption($input, 'price') ?? $weapon->getPrice());
            $weapon->setWeight($this->parseFloatOption($input, 'weight') ?? $weapon->getWeight());
            $weapon->setWeaponCategory(
                $this->parseRulesWeaponCategoryIdOption($input, 'weapon-category-id') ?? $weapon->getWeaponCategory()
            );
            $weapon->setSource($this->parseRulesSourceIdOption($input, 'source-id') ?? $weapon->getSource());

            $description = $this->parseStringOption($input, 'description', true);
            $weapon->setDescription($description === false ? $weapon->getDescription() : $description);

            $weaponSize = $this->parseRulesWeaponSizeIdOption($input, 'weapon-size-id');
            $weapon->setWeaponSize($weaponSize === false ? $weapon->getWeaponSize() : $weaponSize);

            $damageRoll = $this->parseStringOption($input, 'damage-roll', true);
            $weapon->setDamageRoll($damageRoll === false ? $weapon->getDamageRoll() : $damageRoll);

            $missileDamageRoll = $this->parseStringOption($input, 'missile-damage-roll', true);
            $weapon->setMissileDamageRoll(
                $missileDamageRoll === false ? $weapon->getMissileDamageRoll() : $missileDamageRoll
            );

            $oneHandedDamageRoll = $this->parseStringOption($input, 'one-handed-damage-roll', true);
            $weapon->setOneHandedDamageRoll(
                $oneHandedDamageRoll === false ? $weapon->getOneHandedDamageRoll() : $oneHandedDamageRoll
            );

            $twoHandedDamageRoll = $this->parseStringOption($input, 'two-handed-damage-roll', true);
            $weapon->setTwoHandedDamageRoll(
                $twoHandedDamageRoll === false ? $weapon->getTwoHandedDamageRoll() : $twoHandedDamageRoll
            );

            $this->validate($weapon);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weapon,
                    [
                        AbstractNormalizer::GROUPS => [
                            RulesWeapon::GROUP_DETAIL,
                            RulesSource::GROUP_LIST,
                            RulesWeaponSize::GROUP_LIST,
                            RulesWeaponCategory::GROUP_LIST
                        ]
                    ]
                ));

                if (!$io->confirm('Update rules weapon?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weapon);
            $this->entityManager->flush();

            $io->success(sprintf('Rules weapon with id %d has been updated.', $weapon->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

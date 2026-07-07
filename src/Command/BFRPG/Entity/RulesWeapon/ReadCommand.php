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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:bfrpg:entity:rules-weapon:read',
    description: 'Read a rules weapon'
)]
final class ReadCommand extends Command
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
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to read a <comment>rules weapon</comment>
                in the <comment>BFRPG</comment> db.

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
        $io->title('BFRPG: Read Rules Weapon');

        try {
            $io->definitionList(...$this->definitionListConverter->convert(
                $this->parseRulesWeaponIdArgument($input, 'id'),
                [
                    AbstractNormalizer::GROUPS => [
                        RulesWeapon::GROUP_DETAIL,
                        RulesSource::GROUP_LIST,
                        RulesWeaponSize::GROUP_LIST,
                        RulesWeaponCategory::GROUP_LIST
                    ]
                ]
            ));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

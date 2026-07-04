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

namespace App\Command\BFRPG\Entity\RulesWeaponSize;

use App\Domain\BFRPG\Console\Command\Command;
use App\Domain\BFRPG\Entity\RulesSource;
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
    name: 'app:bfrpg:entity:rules-weapon-size:update',
    description: 'Update a rules weapon size'
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
                description: 'The id of the rules weapon size'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the rules weapon size'
            )
            ->addOption(
                name: 'short-name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The short name of the rules weapon size'
            )
            ->addOption(
                name: 'source-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules source id for the weapon size'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a
                <comment>rules weapon size</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--name <name>] [--short-name <short-name>] [--source-id <source-id>]</info>

                Examples:
                  <info>%command.full_name% 3 --name Large --short-name L</info>

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
        $this->interactRulesWeaponSize($input, $output, 'id', 'Rules weapon size: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Update Rules Weapon Size');

        try {
            $weaponSize = $this->parseRulesWeaponSizeIdArgument($input, 'id');

            $weaponSize->setName($this->parseStringOption($input, 'name', true) ?? $weaponSize->getName());
            $weaponSize->setShortName($this->parseStringOption($input, 'short-name', true) ?? $weaponSize->getShortName());
            $weaponSize->setSource($this->parseRulesSourceIdOption($input, 'source-id') ?? $weaponSize->getSource());

            $this->validate($weaponSize);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weaponSize,
                    [
                        AbstractNormalizer::GROUPS => [RulesWeaponSize::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Update rules weapon size?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weaponSize);
            $this->entityManager->flush();

            $io->success(sprintf('Rules weapon size with id %d has been updated.', $weaponSize->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

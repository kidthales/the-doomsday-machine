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

namespace App\Command\BFRPG\Entity\RulesRangeCategory;

use App\Domain\BFRPG\Console\Command\Command;
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
    name: 'app:bfrpg:entity:rules-range-category:create',
    description: 'Create a rules range category'
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
                description: 'The name of the rules range category'
            )
            ->addArgument(
                name: 'modifier',
                mode: InputArgument::REQUIRED,
                description: 'The modifier for the rules range category'
            )
            ->addArgument(
                name: 'source-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules source id for the range category'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>rules range category</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <name> <modifier> <source-id></info>

                Examples:
                  <info>%command.full_name% Short 1 1 </info>

                If no name, modifier, or source id is specified, you'll be prompted interactively.
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
        $this->interactQuestion($input, $output, 'name', 'Rules range category name: ');
        $this->interactQuestion($input, $output, 'modifier', 'Rules range category modifier: ');
        $this->interactRulesSource($input, $output, 'source-id', 'Rules range category sourced from: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Create Rules range Category');

        try {
            $rangeCategory = (new RulesrangeCategory())
                ->setName($this->parseStringArgument($input, 'name', true))
                ->setModifier($this->parseIntArgument($input, 'modifier'))
                ->setSource($this->parseRulesSourceIdArgument($input, 'source-id'));

            $this->validate($rangeCategory);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $rangeCategory,
                    [
                        AbstractNormalizer::GROUPS => [RulesRangeCategory::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Create rules range category?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($rangeCategory);
            $this->entityManager->flush();

            $io->success(sprintf('Rules range category has been created with id %d.', $rangeCategory->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

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

namespace App\Command\BFRPG\Entity\RulesItemRangeCategoryDistance;

use App\Domain\BFRPG\Console\Command\Command;
use App\Domain\BFRPG\Entity\RulesItem;
use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesItemRangeCategoryDistance;
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
    name: 'app:bfrpg:entity:rules-item-range-category-distance:update',
    description: 'Update a rules item range category distance'
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
                description: 'The id of the rules item range category distance'
            )
            ->addOption(
                name: 'item-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules item id for the item range category distance'
            )
            ->addOption(
                name: 'range-category-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules range category id for the item range category distance'
            )
            ->addOption(
                name: 'distance',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The distance of the rules item range category distance'
            )
            ->addOption(
                name: 'source-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules source id for the item range category distance'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a
                <comment>rules range category</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <id>
                    [--item-id <item-id>] [--range-category-id <range-category-id>]
                    [--distance <distance>] [--source-id <source-id>]</info>

                Examples:
                  <info>%command.full_name% 1 --distance 10 </info>

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
        $this->interactRulesItemRangeCategoryDistance($input, $output, 'id', 'Rules item range category distance: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Update Rules Item Range Category Distance');

        try {
            $itemRangeCategoryDistance = $this->parseRulesItemRangeCategoryDistanceIdArgument($input, 'id');

            $itemRangeCategoryDistance->setItem(
                $this->parseRulesItemIdOption($input, 'item-id') ?? $itemRangeCategoryDistance->getItem()
            );
            $itemRangeCategoryDistance->setRangeCategory(
                $this->parseRulesRangeCategoryIdOption($input, 'range-category-id') ?? $itemRangeCategoryDistance->getRangeCategory()
            );
            $itemRangeCategoryDistance->setDistance(
                $this->parseIntOption($input, 'distance') ?? $itemRangeCategoryDistance->getDistance()
            );
            $itemRangeCategoryDistance->setSource(
                $this->parseRulesSourceIdOption($input, 'source-id') ?? $itemRangeCategoryDistance->getSource()
            );

            $this->validate($itemRangeCategoryDistance);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $itemRangeCategoryDistance,
                    [
                        AbstractNormalizer::GROUPS => [
                            RulesItemRangeCategoryDistance::GROUP_DETAIL,
                            RulesItem::GROUP_LIST,
                            RulesRangeCategory::GROUP_LIST,
                            RulesSource::GROUP_LIST
                        ]
                    ]
                ));

                if (!$io->confirm('Update rules item range category distance?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($itemRangeCategoryDistance);
            $this->entityManager->flush();

            $io->success(sprintf('Rules item range category distance with id %d has been updated.', $itemRangeCategoryDistance->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

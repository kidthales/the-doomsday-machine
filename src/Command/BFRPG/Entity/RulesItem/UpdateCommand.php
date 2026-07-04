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

namespace App\Command\BFRPG\Entity\RulesItem;

use App\Domain\BFRPG\Console\Command\Command;
use App\Domain\BFRPG\Entity\RulesItem;
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
    name: 'app:bfrpg:entity:rules-item:update',
    description: 'Update a rules item'
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
                description: 'The id of the rules item'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the rules item'
            )
            ->addOption(
                name: 'price',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The price of the rules item'
            )
            ->addOption(
                name: 'weight',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The weight of the rules item'
            )
            ->addOption(
                name: 'source-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The rules source id for the item'
            )
            ->addOption(
                name: 'description',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The description of the rules item',
                default: false
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a <comment>rules item</comment>
                in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--name <name>] [--price <price>] [--weight <weight>] [--source-id <source-id>] [--description [<description>]]</info>

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
        $this->interactRulesItem($input, $output, 'id', 'Rules item: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Update Rules Item');

        try {
            $item = $this->parseRulesItemArgument($input, 'id');

            $item->setName($this->parseStringOption($input, 'name', true) ?? $item->getName());
            $item->setPrice($this->parseFloatOption($input, 'price') ?? $item->getPrice());
            $item->setWeight($this->parseFloatOption($input, 'weight') ?? $item->getWeight());
            $item->setSource($this->parseRulesSourceOption($input, 'source-id') ?? $item->getSource());

            $description = $this->parseStringOption($input, 'description', true);
            $item->setDescription($description === false ? $item->getDescription() : $description);

            $this->validate($item);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $item,
                    [
                        AbstractNormalizer::GROUPS => [RulesItem::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Update rules item?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($item);
            $this->entityManager->flush();

            $io->success(sprintf('Rules item with id %d has been updated.', $item->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

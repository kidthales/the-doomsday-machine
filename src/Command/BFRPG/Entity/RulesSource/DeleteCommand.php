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

namespace App\Command\BFRPG\Entity\RulesSource;

use App\Domain\BFRPG\Console\Command\Command;
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
    name: 'app:bfrpg:entity:rules-source:delete',
    description: 'Delete a rules source'
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
                description: 'The id of the rules source'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to delete a <comment>rules source</comment>
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
        $this->interactRulesSource($input, $output, 'id', 'Rules source: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Delete Rules Source');

        try {
            $source = $this->parseRulesSourceIdArgument($input, 'id');

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $source,
                    [
                        AbstractNormalizer::GROUPS => RulesSource::GROUP_DETAIL
                    ]
                ));

                $numItems = $source->getItems()->count();
                if ($numItems > 0) {
                    $io->warning(sprintf('%d rules items will also be deleted!', $numItems));
                }

                $numWeaponSizes = $source->getWeaponSizes()->count();
                if ($numWeaponSizes > 0) {
                    $io->warning(sprintf('%d rules weapon sizes will also be deleted!', $numWeaponSizes));
                }

                $numWeaponCategories = $source->getWeaponCategories()->count();
                if ($numWeaponCategories > 0) {
                    $io->warning(sprintf('%d rules weapon categories will also be deleted!', $numWeaponCategories));
                }

                $numWeapons = $source->getWeapons()->count();
                if ($numWeapons > 0) {
                    $io->warning(sprintf('%d rules weapons will also be deleted!', $numWeapons));
                }

                $numRangeCategories = $source->getRangeCategories()->count();
                if ($numRangeCategories > 0) {
                    $io->warning(sprintf('%d rules range categories will also be deleted!', $numRangeCategories));
                }

                $numItemRangeCategoryDistances = $source->getItemRangeCategoryDistances()->count();
                if ($numItemRangeCategoryDistances > 0) {
                    $io->warning(
                        sprintf(
                            '%d rules item range category distances will also be deleted!',
                            $numItemRangeCategoryDistances
                        )
                    );
                }

                $numWeaponRangeCategoryDistances = $source->getWeaponRangeCategoryDistances()->count();
                if ($numWeaponRangeCategoryDistances > 0) {
                    $io->warning(
                        sprintf(
                            '%d rules weapon range category distances will also be deleted!',
                            $numWeaponRangeCategoryDistances
                        )
                    );
                }

                $numArmors = $source->getArmors()->count();
                if ($numArmors > 0) {
                    $io->warning(sprintf('%d rules armors will also be deleted!', $numArmors));
                }

                if (!$io->confirm('Delete rules source?')) {
                    return Command::SUCCESS;
                }
            }

            $id = $source->getId();

            $this->entityManager->remove($source);
            $this->entityManager->flush();

            $io->success(sprintf('Rules source with id %d has been deleted.', $id));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

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

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
use App\Domain\BFRPG\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
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
    name: 'app:bfrpg:entity:rules-weapon-size:create',
    description: 'Create a rules weapon size'
)]
final class CreateCommand extends Command
{
    use EntityManagerAwareTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the rules weapon size'
            )
            ->addArgument(
                name: 'short-name',
                mode: InputArgument::REQUIRED,
                description: 'The short name of the rules weapon size'
            )
            ->addArgument(
                name: 'source-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules source id for the weapon size'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>rules weapon size</comment> in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <name> <short-name> <source-id></info>

                Examples:
                  <info>%command.full_name% Small S 1 </info>

                If no name, short name, or source id is specified, you'll be prompted interactively.
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
        $this->interactQuestion($input, $output, 'name', 'Rules weapon size name: ');
        $this->interactQuestion($input, $output, 'short-name', 'Rules weapon size short name: ');
        $this->interactChoiceQuestionWithChoosables(
            $input,
            $output,
            'source-id',
            'Rules item sourced from: ',
            $this->entityManager->getRepository(RulesSource::class)->findAll(),
            true
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Create Rules Weapon Size');

        try {
            $source = $this->entityManager->find(RulesSource::class, $input->getArgument('source-id'));
            if ($source === null) {
                $io->error('Rules source not found');
                return Command::FAILURE;
            }

            $weaponSize = (new RulesWeaponSize())
                ->setName(trim($input->getArgument('name')))
                ->setShortName(trim($input->getArgument('short-name')))
                ->setSource($source);

            $errors = $this->validator->validate($weaponSize);
            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $weaponSize,
                    [
                        AbstractNormalizer::GROUPS => [RulesWeaponSize::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Create rules weapon size?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($weaponSize);
            $this->entityManager->flush();

            $io->success(
                sprintf(
                    'Rules weapon size %s has been created with id %d.',
                    $weaponSize->getChoiceValue(),
                    $weaponSize->getId()
                )
            );
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

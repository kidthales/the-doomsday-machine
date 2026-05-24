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

namespace App\Command\BFRPG\Rules\Source;

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:bfrpg:rules:source:update',
    description: 'Update a rules source',
    aliases: ['app:bf:rls:src:update'],
)]
final class UpdateCommand extends Command
{
    /**
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $bfrpgEntityManager Autowiring alias
     * @param DefinitionListConverter $definitionListConverter
     */
    public function __construct(
        private readonly ValidatorInterface      $validator,
        private readonly EntityManagerInterface  $bfrpgEntityManager,
        private readonly DefinitionListConverter $definitionListConverter
    )
    {
        parent::__construct();
    }

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
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the rules source'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a <comment>rules source</comment>
                in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--name <name>]</info>

                Examples:
                  <info>%command.full_name% 1 --name "Core Rules 5th Edition"</info>

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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if ($input->getArgument('id') === null) {
            $input->setArgument('id', $helper->ask($input, $output, new Question('Rules source id: ')));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('BFRPG: Rules Source Update');

        try {
            $source = $this->bfrpgEntityManager->find(RulesSource::class, $input->getArgument('id'));

            if ($source === null) {
                $io->error('Rules source not found');
                return Command::FAILURE;
            }

            $source->setName($input->getOption('name') ?? $source->getName());

            $errors = $this->validator->validate($source);

            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $source,
                    [
                        AbstractNormalizer::GROUPS => RulesSource::GROUP_DETAIL
                    ]
                ));

                if (!$io->confirm('Update rules source?')) {
                    return Command::SUCCESS;
                }
            }

            $this->bfrpgEntityManager->persist($source);
            $this->bfrpgEntityManager->flush();

            $io->success(sprintf('Rules source with id %d has been updated.', $source->getId()));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

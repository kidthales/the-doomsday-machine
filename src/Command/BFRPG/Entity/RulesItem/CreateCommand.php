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

use App\Domain\BFRPG\Entity\RulesItem;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Repository\RulesSourceRepository;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:bfrpg:entity:rules-item:create',
    description: 'Create a rules item'
)]
final class CreateCommand extends Command
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
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the rules item'
            )
            ->addArgument(
                name: 'price',
                mode: InputArgument::REQUIRED,
                description: 'The price of the rules item'
            )
            ->addArgument(
                name: 'weight',
                mode: InputArgument::REQUIRED,
                description: 'The weight of the rules item'
            )
            ->addArgument(
                name: 'source-id',
                mode: InputArgument::REQUIRED,
                description: 'The rules source id for the item'
            )
            ->addOption(
                name: 'description',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The description of the rules item'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a <comment>rules item</comment>
                in the <comment>BFRPG</comment> db.

                Usage:
                  <info>%command.full_name% <name> <price> <weight> <source-id> [--description [<description>]]</info>

                Examples:
                  <info>%command.full_name% "Iron Spike" 0.08 0.08 1 --description "An Iron Spike is useful for spiking doors closed (or spiking them open) and may be used as crude pitons in appropriate situations."</info>

                If no name, price, weight, or source id is specified, you'll be prompted interactively.
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

        if ($input->getArgument('name') === null) {
            $input->setArgument('name', $helper->ask($input, $output, new Question('Rules item name: ')));
        }

        if ($input->getArgument('price') === null) {
            $input->setArgument('price', $helper->ask($input, $output, new Question('Rules item price: ')));
        }

        if ($input->getArgument('weight') === null) {
            $input->setArgument('weight', $helper->ask($input, $output, new Question('Rules item weight: ')));
        }

        if ($input->getArgument('source-id') === null) {
            /** @var RulesSourceRepository $repo */
            $repo = $this->bfrpgEntityManager->getRepository(RulesSource::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Rules item sourced from: ', $choices));
                $input->setArgument('source-id', array_search($choice, $choices, true));
            }
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
        $io->title('BFRPG: Create Rules Item');

        try {
            $price = $input->getArgument('price');
            if (!is_numeric($price)) {
                $io->error('The price argument must be a numeric value.');
                return Command::FAILURE;
            }

            $weight = $input->getArgument('weight');
            if (!is_numeric($weight)) {
                $io->error('The weight argument must be a numeric value.');
                return Command::FAILURE;
            }

            $source = $this->bfrpgEntityManager->find(RulesSource::class, $input->getArgument('source-id'));
            if ($source === null) {
                $io->error('Rules source not found');
                return Command::FAILURE;
            }

            $description = $input->getOption('description');
            if ($description !== null) {
                $description = trim($description);
            }

            $item = (new RulesItem())
                ->setName(trim($input->getArgument('name')))
                ->setPrice(floatval($price))
                ->setWeight(floatval($weight))
                ->setDescription($description)
                ->setSource($source);

            $errors = $this->validator->validate($item);

            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $item,
                    [
                        AbstractNormalizer::GROUPS => [RulesItem::GROUP_DETAIL, RulesSource::GROUP_LIST]
                    ]
                ));

                if (!$io->confirm('Create rules item?')) {
                    return Command::SUCCESS;
                }
            }

            $this->bfrpgEntityManager->persist($item);
            $this->bfrpgEntityManager->flush();

            $io->success(sprintf('Rules item %s has been created with id %d.', $item->getChoiceValue(), $item->getId()));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

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
use App\Domain\BFRPG\Repository\RulesItemRepository;
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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use UnexpectedValueException;

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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        if ($input->getArgument('id') === null) {
            /** @var RulesItemRepository $repo */
            $repo = $this->bfrpgEntityManager->getRepository(RulesItem::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Rules item id: ', $choices));
                $input->setArgument('id', array_search($choice, $choices, true));
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
        $io->title('BFRPG: Rules Item Update');

        try {
            $item = $this->bfrpgEntityManager->find(RulesItem::class, $input->getArgument('id'));

            if ($item === null) {
                $io->error('Rules item not found');
                return Command::FAILURE;
            }

            $price = $input->getOption('price');
            if ($price !== null) {
                if (!is_numeric($price)) {
                    throw new UnexpectedValueException('The price option must be a numeric value.');
                }
                $price = floatval($price);
            }

            $weight = $input->getOption('weight');
            if ($weight !== null) {
                if (!is_numeric($weight)) {
                    throw new UnexpectedValueException('The weight option must be a numeric value.');
                }
                $weight = floatval($weight);
            }

            $source = null;
            $sourceId = $input->getOption('source-id');
            if ($sourceId !== null) {
                $source = $this->bfrpgEntityManager->find(RulesSource::class, $sourceId);
                if ($source === null) {
                    $io->error('Rules source not found');
                    return Command::FAILURE;
                }
            }

            $description = $input->getOption('description');
            if ($description === false) {
                $description = $item->getDescription();
            }
            if ($description !== null) {
                $description = trim($description);
            }

            $item->setName(trim($input->getOption('name') ?? $item->getName()));
            $item->setPrice($price ?? $item->getPrice());
            $item->setWeight($weight ?? $item->getWeight());
            $item->setDescription($description);
            $item->setSource($source ?? $item->getSource());

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

                if (!$io->confirm('Update rules item?')) {
                    return Command::SUCCESS;
                }
            }

            $this->bfrpgEntityManager->persist($item);
            $this->bfrpgEntityManager->flush();

            $io->success(sprintf('Rules item with id %d has been updated.', $item->getId()));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

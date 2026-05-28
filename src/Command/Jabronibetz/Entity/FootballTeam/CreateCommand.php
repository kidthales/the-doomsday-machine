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

namespace App\Command\Jabronibetz\Entity\FootballTeam;

use App\Domain\Jabronibetz\Entity\FootballOrganization;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Enum\FootballGender;
use App\Domain\Jabronibetz\Repository\FootballOrganizationRepository;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
    name: 'app:jabronibetz:entity:football-team:create',
    description: 'Create a football team',
    aliases: ['app:jbetz:footy:team:create'],
)]
final class CreateCommand extends Command
{
    /**
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $jabronibetzEntityManager Autowiring alias
     * @param DefinitionListConverter $definitionListConverter
     */
    public function __construct(
        private readonly ValidatorInterface      $validator,
        private readonly EntityManagerInterface  $jabronibetzEntityManager,
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
        $availableGenders = '<info>' . implode('</info>, <info>', array_column(FootballGender::cases(), 'value')) . '</info>';

        $this
            ->addArgument(
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the football team'
            )
            ->addArgument(
                name: 'short-name',
                mode: InputArgument::REQUIRED,
                description: 'The short name of the football team'
            )
            ->addArgument(
                name: 'organization-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football organization that manages the team'
            )
            ->addArgument(
                name: 'gender',
                mode: InputArgument::REQUIRED,
                description: 'The gender of the football team'
            )
            ->setHelp(
                <<<HELP
                The <info>%command.name%</info> command allows you to create a <comment>football team</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <name> <short-name> <organization-id> <gender></info>

                Examples:
                  <info>%command.full_name% Mexico MEX 10 male</info>

                If no name, short name, organization id, or gender is specified, you'll be prompted interactively.
                Gender may be one of $availableGenders.
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
            $input->setArgument('name', $helper->ask($input, $output, new Question('Football team name: ')));
        }

        if ($input->getArgument('short-name') === null) {
            $input->setArgument('short-name', $helper->ask($input, $output, new Question('Football team short name: ')));
        }

        if ($input->getArgument('organization-id') === null) {
            /** @var FootballOrganizationRepository $repo */
            $repo = $this->jabronibetzEntityManager->getRepository(FootballOrganization::class);
            $choices = $repo->findAllChoices();

            if (!empty($choices)) {
                $choice = $helper->ask($input, $output, new ChoiceQuestion('Football team managed by: ', $choices));
                $input->setArgument('organization-id', array_search($choice, $choices, true));
            }
        }

        if ($input->getArgument('gender') === null) {
            $input->setArgument(
                'gender',
                $helper->ask(
                    $input,
                    $output,
                    new ChoiceQuestion('Football team gender: ', array_column(FootballGender::cases(), 'value'))
                )
            );
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
        $io->title('Jabronibetz: Football Team Create');

        try {
            $org = $this->jabronibetzEntityManager->find(FootballOrganization::class, $input->getArgument('organization-id'));
            if ($org === null) {
                $io->error('Football organization not found');
                return Command::FAILURE;
            }

            $team = (new FootballTeam())
                ->setName(trim($input->getArgument('name')))
                ->setShortName(trim($input->getArgument('short-name')))
                ->setManagingOrganization($org)
                ->setGender(FootballGender::from($input->getArgument('gender')));

            $errors = $this->validator->validate($team);
            if (count($errors) > 0) {
                $io->error((string)$errors);
                return Command::FAILURE;
            }

            if ($input->isInteractive()) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $team,
                    [
                        AbstractNormalizer::GROUPS => FootballTeam::GROUP_DETAIL
                    ]
                ));

                if (!$io->confirm('Create football team?')) {
                    return Command::SUCCESS;
                }
            }

            $this->jabronibetzEntityManager->persist($team);
            $this->jabronibetzEntityManager->flush();

            $io->success(sprintf(
                'Football team %s has been created with id %d.',
                $team->getChoiceValue(),
                $team->getId()
            ));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

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

namespace App\Command\Jabronibetz;

use App\Domain\Jabronibetz\Calculator\FootballCalculatorAwareTrait;
use App\Domain\Jabronibetz\DataProvider\FootballCompetitionDataProviderAwareTrait;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use App\Domain\Shared\Console\Style\DefinitionListConverterAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football-match-xg:list',
    description: 'List football match xg for given competition'
)]
final class FootballMatchXGListCommand extends Command
{
    use DefinitionListConverterAwareTrait,
        EntityManagerAwareTrait,
        FootballCompetitionDataProviderAwareTrait;

    private const array HEADERS = [
        'Match',
        'Home XG (Seed)',
        'Away XG (Seed)',
        'Home XG (Strength)',
        'Away XG (Strength)',
        'Home XG (Lerp)',
        'Away XG (Lerp)',
        't'
    ];

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'competition-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football competition'
            )
            ->addOption(
                name: 'group',
                mode: InputOption::VALUE_NONE,
                description: 'List & calculate football match xg by competition group'
            )
            ->addOption(
                name: 'limit',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Limit quantity the of football match xg displayed'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to list the <comment>football team strength</comment>s
                for a <comment>football competition</comment> that exists in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id> [--group] [--limit <limit>]</info>

                Examples:
                  <info>%command.full_name% 1 --limit 32</info>

                If no competition-id is specified, you'll be prompted interactively.
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
        $this->interactChoiceQuestionWithChoosables(
            $input,
            $output,
            'competition-id',
            'Football competition id: ',
            $this->entityManager->getRepository(FootballCompetition::class)->findAll(),
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
        $io->title('Jabronibetz: List Football Match XGs');

        try {
            $competition = $this->entityManager->find(FootballCompetition::class, $input->getArgument('competition-id'));
            if ($competition === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $io->section($competition->getName());

            $group = $input->getOption('group');
            $limit = $input->getOption('limit');
            if ($limit !== null) {
                if (!is_numeric($limit)) {
                    $io->error('Limit quantity must be a numeric value');
                    return Command::FAILURE;
                }
                $limit = intval($limit);
            }
            $matchXGs = $this->footballCompetitionDataProvider->getMatchXGLerps($competition, $group, $limit);

            if ($group) {
                foreach ($matchXGs as $matchXGGroup => $groupMatchXGs) {
                    $rows = [];
                    foreach ($groupMatchXGs as $groupMatchXG) {
                        $rows[] = [
                            $this->entityManager->find(FootballMatch::class, $groupMatchXG->matchId)->getChoiceValue(),
                            $groupMatchXG->a->homeTeam,
                            $groupMatchXG->a->awayTeam,
                            $groupMatchXG->b->homeTeam,
                            $groupMatchXG->b->awayTeam,
                            $groupMatchXG->homeTeam,
                            $groupMatchXG->awayTeam,
                            $groupMatchXG->t
                        ];
                    }
                    $table = new Table($output);
                    $table->setHeaderTitle(sprintf('Group %s', $matchXGGroup));
                    $table->setHeaders(self::HEADERS);
                    $table->setRows($rows);
                    $table->render();
                }
            } else {
                $rows = [];
                foreach ($matchXGs as $matchXG) {
                    $rows[] = [
                        $this->entityManager->find(FootballMatch::class, $matchXG->matchId)->getChoiceValue(),
                        $matchXG->a->homeTeam,
                        $matchXG->a->awayTeam,
                        $matchXG->b->homeTeam,
                        $matchXG->b->awayTeam,
                        $matchXG->homeTeam,
                        $matchXG->awayTeam,
                        $matchXG->t
                    ];
                }
                $io->table(self::HEADERS, $rows);
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

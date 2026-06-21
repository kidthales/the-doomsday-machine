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

use App\Domain\Jabronibetz\DataProvider\FootballCompetitionDataProviderAwareTrait;
use App\Domain\Jabronibetz\DTO\FootballTeamStrength;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
    name: 'app:jabronibetz:football-team-strength:list',
    description: 'List football team strengths for given competition'
)]
final class FootballTeamStrengthListCommand extends Command
{
    use EntityManagerAwareTrait, FootballCompetitionDataProviderAwareTrait;

    private const array HEADERS = ['Team', 'Attack', 'Defense'];

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
                description: 'List & calculate football team strengths by competition group'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to list the <comment>football team strength</comment>s
                for a <comment>football competition</comment> that exists in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id> [--group]</info>

                Examples:
                  <info>%command.full_name% 1</info>

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


        try {
            $competition = $this->entityManager->find(FootballCompetition::class, $input->getArgument('competition-id'));
            if ($competition === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $io->title(sprintf('Jabronibetz: List Football Team Strengths - %s', $competition->getName()));

            $group = $input->getOption('group');
            $teamStrengths = $this->footballCompetitionDataProvider->getTeamStrengths($competition, $group);
            if ($group) {
                foreach ($teamStrengths as $teamGroup => $teamGroupStrengths) {
                    $table = new Table($output);
                    $table->setHeaderTitle(sprintf('Group %s', $teamGroup));
                    $table->setHeaders(self::HEADERS);
                    $table->setRows($this->formatTeamStrengths($teamGroupStrengths));
                    $table->render();
                }
            } else {
                $io->table(self::HEADERS, $this->formatTeamStrengths($teamStrengths));
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param FootballTeamStrength[] $teamStrengths
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function formatTeamStrengths(array $teamStrengths): array
    {
        $formattedTeamStrengths = array_reduce(
            $teamStrengths,
            function (array $rows, FootballTeamStrength $teamStrength) {
                $rows[] = [
                    $this->entityManager->find(FootballTeam::class, $teamStrength->teamId)?->getName() ?? 'Unknown',
                    $teamStrength->attack,
                    $teamStrength->defense
                ];
                return $rows;
            },
            []
        );
        usort($formattedTeamStrengths, fn(array $a, array $b) => strcmp($a[0], $b[0]));
        return $formattedTeamStrengths;
    }
}

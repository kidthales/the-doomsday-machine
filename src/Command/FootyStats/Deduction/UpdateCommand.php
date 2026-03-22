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

namespace App\Command\FootyStats\Deduction;

use App\Console\Command\FootyStats\AbstractTargetCommand as Command;
use App\Database\FootyStats\DeductionTableAwareTrait;
use Doctrine\DBAL\Exception as DBALException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:deduction:update',
    description: 'Update point deductions for a given team',
)]
final class UpdateCommand extends Command
{
    use DeductionTableAwareTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('team', InputArgument::REQUIRED, 'Team name')
            ->addArgument('points', InputArgument::REQUIRED, 'Total points deducted');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws DBALException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);
        $target = $this->getTargetArguments($input);

        $team = $input->getArgument('team');

        if (!$team) {
            $teams = $this->footyStatsDeductionTable
                ->createSelectQueryBuilder($target)
                ->select('team_name')
                ->orderBy('team_name')
                ->fetchFirstColumn();

            if (!empty($teams)) {
                $team = $this->io->choice('Choose Team', $teams);
                $input->setArgument('team', $team);
            }
        }

        $points = $input->getArgument('points');

        if (!$points) {
            $currentPoints = $this->footyStatsDeductionTable
                ->createSelectQueryBuilder($target)
                ->select('points')
                ->where('team_name = :team_name')
                ->setParameter('team_name', $input->getArgument('team'))
                ->fetchOne();

            if ($currentPoints === false) {
                throw new RuntimeException(sprintf('Invalid team argument: %s', $team));
            }

            $input->setArgument('points', $this->io->ask('Input total points deducted', (string)$currentPoints));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);

        $team = $input->getArgument('team');
        $points = (int)$input->getArgument('points');

        $this->footyStatsDeductionTable
            ->createUpdateQueryBuilder($target)
            ->where('team_name = :team_name')
            ->setParameter('team_name', $team)
            ->set('points', ':points')
            ->setParameter('points', $points)
            ->executeStatement();

        $this->io->success(sprintf('Updated %s to %d points deducted', $team, $points));

        return Command::SUCCESS;
    }
}

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
use App\Console\Command\PrettyOptionTrait;
use App\Database\FootyStats\DeductionTableAwareTrait;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:deduction:list',
    description: 'List point deductions',
)]
final class ListCommand extends Command
{
    use DeductionTableAwareTrait, PrettyOptionTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
        $this->configureCommandPrettyOption();
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

        $selectQueryBuilder = $this->footyStatsDeductionTable
            ->createSelectQueryBuilder($target)
            ->select('*')
            ->orderBy('team_name');

        $deductions = $selectQueryBuilder->fetchAllAssociative();

        if ($this->getCommandPrettyOption($input)) {
            $deductions = array_map(
                fn(array $deduction) => [
                    'Team' => $deduction['team_name'],
                    'Pts' => $deduction['points'],
                    'Extra' => $deduction['extra']
                ],
                $deductions
            );
        }

        $this->io->table(array_keys($deductions[0]), $deductions);

        return Command::SUCCESS;
    }
}

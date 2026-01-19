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

namespace App\Command\FootyStats\TeamStrength;

use App\Console\Command\DataOptionsTrait;
use App\Console\Command\FootyStats\AbstractTargetCommand as Command;
use App\Console\Command\PrettyOptionTrait;
use App\Database\FootyStats\TeamStrengthViewAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:team-strength:list',
    description: 'List team strengths',
)]
final class ListCommand extends Command
{
    use DataOptionsTrait, PrettyOptionTrait, TeamStrengthViewAwareTrait;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->configureCommandPrettyOption()
            ->configureCommandDataOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getCommandDataOptions($input);

        try {
            $teamStrengths = $this->footyStatsTeamStrengthView
                ->createSelectQueryBuilder($target)
                ->select('*')
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new RuntimeException('Error getting team strengths', previous: $e);
        }

        if (empty($teamStrengths)) {
            if ($dataOutputOptions['json']) {
                $this->io->writeln('[]');
            }

            return Command::SUCCESS;
        }

        if ($this->getCommandPrettyOption($input)) {
            $teamStrengths = array_map(
                fn(array $teamStrength) => [
                    'Team' => $teamStrength['team_name'],
                    'Attack' => number_format(round($teamStrength['attack'], 2), 2),
                    'Defense' => number_format(round($teamStrength['defense'], 2), 2)
                ],
                $teamStrengths
            );
        }

        $columns = array_keys($teamStrengths[0]);

        try {
            if ($dataOutputOptions['json']) {
                $this->io->json($teamStrengths);
            } else if ($dataOutputOptions['csv']) {
                $this->io->csv($columns, $teamStrengths);
            } else {
                $this->io->table($columns, $teamStrengths);
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Error displaying team strengths', previous: $e);
        }

        return Command::SUCCESS;
    }
}

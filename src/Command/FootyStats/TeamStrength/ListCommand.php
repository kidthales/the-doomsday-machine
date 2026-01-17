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
use App\Console\Command\FootyStats\AbstractCommand as Command;
use App\Console\Command\PrettyOptionTrait;
use App\Database\FootyStats\TeamStrengthView;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;
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
    use DataOptionsTrait, PrettyOptionTrait;

    private TeamStrengthView $teamStrengthView;

    #[Required]
    public function setTeamStrengthView(TeamStrengthView $teamStrengthView): void
    {
        $this->teamStrengthView = $teamStrengthView;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->configurePrettyOption()
            ->configureDataOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getDataOptions($input);

        try {
            $teamStrengths = $this->teamStrengthView
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

        if ($this->getPrettyOption($input)) {
            $teamStrengths = array_map(
                fn (array $teamStrength) => [
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

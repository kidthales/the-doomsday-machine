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

namespace App\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait DataOptionsTrait
{
    protected function configureCommandDataOptions(): self
    {
        return $this
            ->addOption('json', mode: InputOption::VALUE_NONE, description: 'Output as JSON')
            ->addOption('csv', mode: InputOption::VALUE_NONE, description: 'Output as CSV');
    }

    /**
     * @param InputInterface $input
     * @return array{json: bool, csv: bool}
     */
    protected function getCommandDataOptions(InputInterface $input): array
    {
        $isJson = $input->getOption('json');
        $isCsv = $input->getOption('csv');

        if ($isJson && $isCsv) {
            throw new RuntimeException("Only one of '--json' or '--csv' may be specified");
        }

        return ['json' => $isJson, 'csv' => $isCsv];
    }
}

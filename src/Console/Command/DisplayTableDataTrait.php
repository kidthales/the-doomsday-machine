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

use JsonException;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait DisplayTableDataTrait
{
    /**
     * @param array $data
     * @param array{json: bool, csv: bool} $options
     * @return void
     * @throws JsonException
     */
    protected function displayCommandTableData(array $data, array $options = []): void
    {
        $columns = array_keys($data[0]);

        if ($options['json']) {
            $this->io->json($data);
        } else if ($options['csv']) {
            $this->io->csv($columns, $data);
        } else {
            $this->io->table($columns, $data);
        }
    }
}

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

namespace App\Console\Style;

use JsonException;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
class DataStyle extends SymfonyStyle
{
    /**
     * @param mixed $data
     * @return void
     * @throws JsonException
     */
    public function json(mixed $data): void
    {
        $this->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    public function csv(array $headers, array $rows): void
    {
        $buffer = fopen('php://memory', 'r+');

        fputcsv($buffer, $headers);

        foreach ($rows as $row) {
            fputcsv($buffer, $row);
        }

        rewind($buffer);

        $this->write(stream_get_contents($buffer));
    }
}

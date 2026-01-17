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

use App\Console\Style\DataStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class AbstractCommand extends Command
{
    public const int SUCCESS = Command::SUCCESS;
    public const int FAILURE = Command::FAILURE;
    public const int INVALID = Command::INVALID;

    protected DataStyle $io;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new DataStyle($input, $output);
    }
}

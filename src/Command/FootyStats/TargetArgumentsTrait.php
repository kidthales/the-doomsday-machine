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

namespace App\Command\FootyStats;

use App\FootyStats\Target;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait TargetArgumentsTrait
{
    protected function configureTargetArguments(): self
    {
        return $this
            ->addArgument('nation', InputArgument::REQUIRED, 'Nation name')
            ->addArgument('competition', InputArgument::REQUIRED, 'Competition name')
            ->addArgument('season', InputArgument::REQUIRED, 'Season identifier');
    }

    protected function getTargetArguments(InputInterface $input): Target
    {
        return new Target(
            $input->getArgument('nation'),
            $input->getArgument('competition'),
            $input->getArgument('season')
        );
    }
}

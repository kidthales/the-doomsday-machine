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

namespace App\Entity\FootyStats;

use Stringable;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
class Target implements Stringable
{
    /**
     * @param string|null $nation
     * @param string|null $competition
     * @param string|null $season
     * @codeCoverageIgnore
     */
    public function __construct(
        public ?string $nation = null,
        public ?string $competition = null,
        public ?string $season = null
    )
    {
    }

    public function __toString(): string
    {
        $s = '';

        if ($this->nation !== null) {
            $s = $this->nation;
        }

        if ($this->competition !== null) {
            $s = sprintf(empty($s) ? '%s%s' : '%s %s', $s, $this->competition);
        }

        if ($this->season !== null) {
            $s = sprintf(empty($s) ? '%s%s' : '%s %s', $s, $this->season);
        }

        return $s;
    }

    public function snake(): string
    {
        $s = '';

        if ($this->nation !== null) {
            $s = s($this->nation)->snake()->toString();
        }

        if ($this->competition !== null) {
            $s = sprintf(empty($s) ? '%s%s' : '%s_%s', $s, s($this->competition)->snake()->toString());
        }

        if ($this->season !== null) {
            $s = sprintf(empty($s) ? '%s%s' : '%s_%s', $s, s($this->season)->snake()->toString());
        }

        return $s;
    }
}

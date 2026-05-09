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

namespace App\Domain\Shared\String;

use function Symfony\Component\String\u;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class TagSearchResult
{
    /**
     * @var string
     */
    public string $subject;

    /**
     * @param string $tag
     */
    public function __construct(public string $tag)
    {
        $this->subject = u($tag)
            // Unescape whitespace
            ->replaceMatches('/\\\\\\s/', fn($m) => u($m[0])->slice(1)->toString())
            ->slice(1)
            ->toString();
    }
}

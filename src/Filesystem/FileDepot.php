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

namespace App\Filesystem;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;

/**
 * A file depot abstraction of the filesystem.
 *
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Autoconfigure(public: true)]
final readonly class FileDepot
{
    /**
     * @param string $fileDepotPath
     */
    public function __construct(#[Autowire(param: 'app.file_depot_path')] private string $fileDepotPath)
    {
    }

    /**
     * Joins the file depot path with the provided path.
     * @param string $path
     * @return string
     */
    public function makePath(string $path): string
    {
        return Path::join($this->fileDepotPath, $path);
    }
}

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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Throwable;

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
     * @param Filesystem $filesystem
     */
    public function __construct(
        #[Autowire(param: 'app.file_depot.path')] private string $fileDepotPath,
        private Filesystem                                       $filesystem
    )
    {
    }

    /**
     * Joins the file depot path with the provided path.
     *
     * @param string $path
     * @return string
     */
    public function makePath(string $path): string
    {
        return Path::join($this->fileDepotPath, $path);
    }

    /**
     * Checks for the presence of one or more files or directories and returns false if any of them is missing.
     *
     * @param iterable|string $files
     * @return bool
     */
    public function exists(iterable|string $files): bool
    {
        return $this->filesystem->exists($this->toArray($files));
    }

    /**
     * Deletes files, directories and symlinks.
     *
     * @param iterable|string $files
     * @return void
     */
    public function remove(iterable|string $files): void
    {
        $this->filesystem->remove($this->toArray($files));
    }

    /**
     * Adds new contents at the end of some file. If either the file or its containing directory doesn't exist, this
     * method creates them before appending the contents.
     *
     * @param string $filename
     * @param resource|string $content
     * @param bool $lock
     * @return void
     */
    public function appendToFile(string $filename, mixed $content, bool $lock = false): void
    {
        $this->filesystem->appendToFile($this->makePath($filename), $content, $lock);
    }

    /**
     * Returns all the contents of a file as a string. Throws an exception when the given file path is not readable and
     * when passing the path to a directory instead of a file.
     *
     * @param string $filename
     * @return string
     */
    public function readFile(string $filename): string
    {
        return $this->filesystem->readFile($this->makePath($filename));
    }

    /**
     * Gets file modification time.
     *
     * @param string $filename
     * @return false|int
     */
    public function filemtime(string $filename): false|int
    {
        $path = $this->makePath($filename);

        if ($this->filesystem->exists($path)) {
            return filemtime($path);
        }

        return false;
    }

    private function toArray(iterable|string $files): iterable
    {
        $paths = [];

        if (!is_iterable($files)) {
            $paths[] = $this->makePath($files);
        } else {
            foreach ($files as $path) {
                $paths[] = $this->makePath($path);
            }
        }

        return $paths;
    }
}

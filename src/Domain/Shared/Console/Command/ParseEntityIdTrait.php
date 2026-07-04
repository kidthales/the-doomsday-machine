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

namespace App\Domain\Shared\Console\Command;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use ValueError;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait ParseEntityIdTrait
{
    /**
     * @param InputInterface $input
     * @param string $argument
     * @param string $type
     * @return mixed|string|null
     */
    protected function parseEntityIdArgument(InputInterface $input, string $argument, string $type): mixed
    {
        $id = $input->getArgument($argument);
        if ($id === null) {
            return null;
        }
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        $entity = $this->entityManager->getRepository($type)->find($id);
        if ($entity === null) {
            throw new RuntimeException(sprintf('%s not found for id %s.', $type, $id));
        }
        return $entity;
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @param string $type
     * @return object|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function parseEntityIdOption(InputInterface $input, string $option, string $type): object|false|null
    {
        $id = $input->getOption($option);
        if ($id === null || $id === false) {
            return $id;
        }
        if (!is_numeric($id)) {
            throw new ValueError(sprintf('The %s option must be a numeric value.', $option));
        }
        $entity = $this->entityManager->find($type, $id);
        if ($entity === null) {
            throw new RuntimeException(sprintf('%s not found for id %s.', $type, $id));
        }
        return $entity;
    }
}

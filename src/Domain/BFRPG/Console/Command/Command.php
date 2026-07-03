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

namespace App\Domain\BFRPG\Console\Command;

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command as BaseCommand;
use App\Domain\Shared\Console\Question\ChoicesResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class Command extends BaseCommand
{
    use EntityManagerAwareTrait;

    const int SUCCESS = BaseCommand::SUCCESS;
    const int FAILURE = BaseCommand::FAILURE;
    const int INVALID = BaseCommand::INVALID;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactRulesSource(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $sourceIdByName = array_reduce(
                $this->entityManager->getRepository(RulesSource::class)->findAll(),
                function (array $carry, RulesSource $source) {
                    $carry[$source->getName()] = $source->getId();
                    return $carry;
                },
                []
            );
            if (!empty($sourceIdByName)) {
                ksort($sourceIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($sourceIdByName),
                );
            }
        }
    }
}

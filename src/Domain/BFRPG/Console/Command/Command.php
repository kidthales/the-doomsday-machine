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

use App\Domain\BFRPG\Entity\RulesItem;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
use App\Domain\BFRPG\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command as BaseCommand;
use App\Domain\Shared\Console\Question\ChoicesResolver;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ValueError;

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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactRulesItem(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $itemIdByName = array_reduce(
                $this->entityManager->getRepository(RulesItem::class)->findAll(),
                function (array $carry, RulesItem $item) {
                    $carry[$item->getName()] = $item->getId();
                    return $carry;
                },
                []
            );
            if (!empty($itemIdByName)) {
                ksort($itemIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($itemIdByName),
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactRulesWeaponCategory(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $weaponCategoryIdByName = array_reduce(
                $this->entityManager->getRepository(RulesWeaponCategory::class)->findAll(),
                function (array $carry, RulesWeaponCategory $weaponCategory) {
                    $carry[$weaponCategory->getName()] = $weaponCategory->getId();
                    return $carry;
                },
                []
            );
            if (!empty($weaponCategoryIdByName)) {
                ksort($weaponCategoryIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($weaponCategoryIdByName),
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactRulesWeaponSize(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $weaponSizeIdByName = array_reduce(
                $this->entityManager->getRepository(RulesWeaponSize::class)->findAll(),
                function (array $carry, RulesWeaponSize $weaponSize) {
                    $carry[$weaponSize->getName()] = $weaponSize->getId();
                    return $carry;
                },
                []
            );
            if (!empty($weaponSizeIdByName)) {
                ksort($weaponSizeIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($weaponSizeIdByName),
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return RulesSource|null
     */
    protected function parseRulesSourceArgument(InputInterface $input, string $argument): ?RulesSource
    {
        $sourceId = $input->getArgument($argument);
        if ($sourceId === null) {
            return null;
        }
        if (!is_numeric($sourceId)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        $source = $this->entityManager->getRepository(RulesSource::class)->find($sourceId);
        if ($source === null) {
            throw new RuntimeException('Rules source not found.');
        }
        return $source;
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return RulesSource|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseRulesSourceOption(InputInterface $input, string $option): RulesSource|false|null
    {
        $sourceId = $input->getOption($option);
        if ($sourceId === null || $sourceId === false) {
            return $sourceId;
        }
        if (!is_numeric($sourceId)) {
            throw new ValueError(sprintf('The %s option must be a numeric value.', $option));
        }
        $source = $this->entityManager->find(RulesSource::class, $sourceId);
        if ($source === null) {
            throw new RuntimeException('Rules source not found.');
        }
        return $source;
    }
}

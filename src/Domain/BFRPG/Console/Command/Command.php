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
use App\Domain\BFRPG\Entity\RulesWeapon;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
use App\Domain\BFRPG\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command as BaseCommand;
use App\Domain\Shared\Console\Command\ParseEntityIdTrait;
use App\Domain\Shared\Console\Question\ChoicesResolver;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class Command extends BaseCommand
{
    use EntityManagerAwareTrait;
    use ParseEntityIdTrait {
        parseEntityIdArgument as private;
        parseEntityIdOption as private;
    }

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
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactRulesWeapon(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $weaponIdByName = array_reduce(
                $this->entityManager->getRepository(RulesWeapon::class)->findAll(),
                function (array $carry, RulesWeapon $weapon) {
                    $carry[$weapon->getName()] = $weapon->getId();
                    return $carry;
                },
                []
            );
            if (!empty($weaponIdByName)) {
                ksort($weaponIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($weaponIdByName),
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return RulesSource|null
     */
    protected function parseRulesSourceIdArgument(InputInterface $input, string $argument): ?RulesSource
    {
        return $this->parseEntityIdArgument($input, $argument, RulesSource::class);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return RulesSource|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseRulesSourceIdOption(InputInterface $input, string $option): RulesSource|false|null
    {
        return $this->parseEntityIdOption($input, $option, RulesSource::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return RulesItem|null
     */
    protected function parseRulesItemIdArgument(InputInterface $input, string $argument): ?RulesItem
    {
        return $this->parseEntityIdArgument($input, $argument, RulesItem::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return RulesWeaponCategory|null
     */
    protected function parseRulesWeaponCategoryIdArgument(InputInterface $input, string $argument): ?RulesWeaponCategory
    {
        return $this->parseEntityIdArgument($input, $argument, RulesWeaponCategory::class);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return RulesWeaponCategory|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseRulesWeaponCategoryIdOption(
        InputInterface $input,
        string         $option
    ): RulesWeaponCategory|false|null
    {
        return $this->parseEntityIdOption($input, $option, RulesWeaponCategory::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return RulesWeaponSize|null
     */
    protected function parseRulesWeaponSizeIdArgument(InputInterface $input, string $argument): ?RulesWeaponSize
    {
        return $this->parseEntityIdArgument($input, $argument, RulesWeaponSize::class);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return RulesWeaponSize|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseRulesWeaponSizeIdOption(InputInterface $input, string $option): RulesWeaponSize|false|null
    {
        return $this->parseEntityIdOption($input, $option, RulesWeaponSize::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return RulesWeapon|null
     */
    protected function parseRulesWeaponIdArgument(InputInterface $input, string $argument): ?RulesWeapon
    {
        return $this->parseEntityIdArgument($input, $argument, RulesWeapon::class);
    }
}

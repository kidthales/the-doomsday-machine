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

namespace App\Domain\Jabronibetz\Console\Command;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballOrganization;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command as BaseCommand;
use App\Domain\Shared\Console\Command\ParseEntityIdTrait;
use App\Domain\Shared\Console\Question\ChoicesResolver;
use Doctrine\Common\Collections\Order;
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
     * @param FootballMatch $match
     * @return string
     */
    protected static function getFootballMatchTitle(FootballMatch $match): string
    {
        $timestamp = $match->getTimestamp();
        return sprintf(
            '%s vs %s (%s) [%s, Round %s]',
            $match->getHomeTeam()?->getName() ?? 'Unknown',
            $match->getAwayTeam()?->getName() ?? 'Unknown',
            $timestamp !== null ? date('Y-m-d H:i:s T', $timestamp) : 'TBD',
            $match->getCompetition()?->getShortName() ?? 'UNK',
            $match->getRound() ?? 'N/A'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactFootballOrganization(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $organizationIdByName = array_reduce(
                $this->entityManager->getRepository(FootballOrganization::class)->findAll(),
                function (array $carry, FootballOrganization $organization) {
                    $name = sprintf('%s (%s)', $organization->getName(), $organization->getShortName());
                    $carry[$name] = $organization->getId();
                    return $carry;
                },
                []
            );
            if (!empty($organizationIdByName)) {
                ksort($organizationIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($organizationIdByName),
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
    protected function interactFootballCompetition(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $competitionIdByName = array_reduce(
                $this->entityManager->getRepository(FootballCompetition::class)->findAll(),
                function (array $carry, FootballCompetition $competition) {
                    $name = sprintf('%s (%s)', $competition->getName(), $competition->getShortName());
                    $carry[$name] = $competition->getId();
                    return $carry;
                },
                []
            );
            if (!empty($competitionIdByName)) {
                ksort($competitionIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($competitionIdByName),
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
    protected function interactFootballTeam(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $teamIdByName = array_reduce(
                $this->entityManager->getRepository(FootballTeam::class)->findAll(),
                function (array $carry, FootballTeam $team) {
                    $name = sprintf('%s (%s) [%s]', $team->getName(), $team->getShortName(), $team->getGender()->value);
                    $carry[$name] = $team->getId();
                    return $carry;
                },
                []
            );
            if (!empty($teamIdByName)) {
                ksort($teamIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($teamIdByName),
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
    protected function interactFootballCompetitionTeamEntry(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $entryIdByName = array_reduce(
                $this->entityManager->getRepository(FootballCompetitionTeamEntry::class)->findAll(),
                function (array $carry, FootballCompetitionTeamEntry $entry) {
                    $competition = $entry->getCompetition();
                    $team = $entry->getTeam();
                    $name = sprintf(
                        '%s - %s',
                        sprintf('%s (%s)', $competition->getName(), $competition->getShortName()),
                        sprintf('%s (%s) [%s]', $team->getName(), $team->getShortName(), $team->getGender()->value)
                    );
                    $carry[$name] = $entry->getId();
                    return $carry;
                },
                []
            );
            if (!empty($entryIdByName)) {
                ksort($entryIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($entryIdByName),
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
    protected function interactFootballMatch(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $matchIdByName = array_reduce(
                $this->entityManager
                    ->getRepository(FootballMatch::class)
                    ->findBy([], ['timestamp' => Order::Ascending->value]),
                function (array $carry, FootballMatch $match) {
                    $carry[static::getFootballMatchTitle($match)] = $match->getId();
                    return $carry;
                },
                []
            );
            if (!empty($matchIdByName)) {
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($matchIdByName),
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballOrganization|null
     */
    protected function parseFootballOrganizationIdArgument(
        InputInterface $input,
        string         $argument
    ): ?FootballOrganization
    {
        return $this->parseEntityIdArgument($input, $argument, FootballOrganization::class);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return FootballOrganization|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseFootballOrganizationIdOption(
        InputInterface $input,
        string         $option
    ): FootballOrganization|false|null
    {
        return $this->parseEntityIdOption($input, $option, FootballOrganization::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballCompetition|null
     */
    protected function parseFootballCompetitionIdArgument(InputInterface $input, string $argument): ?FootballCompetition
    {
        return $this->parseEntityIdArgument($input, $argument, FootballCompetition::class);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return FootballCompetition|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseFootballCompetitionIdOption(
        InputInterface $input,
        string         $option
    ): FootballCompetition|false|null
    {
        return $this->parseEntityIdOption($input, $option, FootballCompetition::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballTeam|null
     */
    protected function parseFootballTeamIdArgument(InputInterface $input, string $argument): ?FootballTeam
    {
        return $this->parseEntityIdArgument($input, $argument, FootballTeam::class);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return FootballTeam|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseFootballTeamIdOption(InputInterface $input, string $option): FootballTeam|false|null
    {
        return $this->parseEntityIdOption($input, $option, FootballTeam::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballCompetitionTeamEntry|null
     */
    protected function parseFootballCompetitionTeamEntryIdArgument(
        InputInterface $input,
        string         $argument
    ): ?FootballCompetitionTeamEntry
    {
        return $this->parseEntityIdArgument($input, $argument, FootballCompetitionTeamEntry::class);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballMatch|null
     */
    protected function parseFootballMatchIdArgument(InputInterface $input, string $argument): ?FootballMatch
    {
        return $this->parseEntityIdArgument($input, $argument, FootballMatch::class);
    }
}

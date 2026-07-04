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
use App\Domain\Shared\Console\Question\ChoicesResolver;
use Doctrine\Common\Collections\Order;
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
    protected function interactFootballOrganization(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $orgIdByName = array_reduce(
                $this->entityManager->getRepository(FootballOrganization::class)->findAll(),
                function (array $carry, FootballOrganization $org) {
                    $name = sprintf('%s (%s)', $org->getName(), $org->getShortName());
                    $carry[$name] = $org->getId();
                    return $carry;
                },
                []
            );
            if (!empty($orgIdByName)) {
                ksort($orgIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($orgIdByName),
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
            $cmpIdByName = array_reduce(
                $this->entityManager->getRepository(FootballCompetition::class)->findAll(),
                function (array $carry, FootballCompetition $cmp) {
                    $name = sprintf('%s (%s)', $cmp->getName(), $cmp->getShortName());
                    $carry[$name] = $cmp->getId();
                    return $carry;
                },
                []
            );
            if (!empty($cmpIdByName)) {
                ksort($cmpIdByName);
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    $argument,
                    $question,
                    new ChoicesResolver($cmpIdByName),
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
                    $cmp = $entry->getCompetition();
                    $team = $entry->getTeam();
                    $name = sprintf(
                        '%s - %s',
                        sprintf('%s (%s)', $cmp->getName(), $cmp->getShortName()),
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
                    $timestamp = $match->getTimestamp();
                    $name = sprintf(
                        '%s vs %s (%s) [%s, Round %s]',
                        $match->getHomeTeam()?->getName() ?? 'Unknown',
                        $match->getAwayTeam()?->getName() ?? 'Unknown',
                        $timestamp !== null ? date('Y-m-d H:i:s T', $timestamp) : 'TBD',
                        $match->getCompetition()?->getShortName() ?? 'UNK',
                        $match->getRound() ?? 'N/A'
                    );
                    $carry[$name] = $match->getId();
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
    protected function parseFootballOrganizationArgument(InputInterface $input, string $argument): ?FootballOrganization
    {
        $organizationId = $input->getArgument($argument);
        if ($organizationId === null) {
            return null;
        }
        if (!is_numeric($organizationId)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        $organization = $this->entityManager->getRepository(FootballOrganization::class)->find($organizationId);
        if ($organization === null) {
            throw new RuntimeException('Football organization not found.');
        }
        return $organization;
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return FootballOrganization|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseFootballOrganizationOption(InputInterface $input, string $option): FootballOrganization|false|null
    {
        $organizationId = $input->getOption($option);
        if ($organizationId === null || $organizationId === false) {
            return $organizationId;
        }
        if (!is_numeric($organizationId)) {
            throw new ValueError(sprintf('The %s option must be a numeric value.', $option));
        }
        $organization = $this->entityManager->find(FootballOrganization::class, $organizationId);
        if ($organization === null) {
            throw new RuntimeException('Football organization not found.');
        }
        return $organization;
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballCompetition|null
     */
    protected function parseFootballCompetitionArgument(InputInterface $input, string $argument): ?FootballCompetition
    {
        $competitionId = $input->getArgument($argument);
        if ($competitionId === null) {
            return null;
        }
        if (!is_numeric($competitionId)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        $competition = $this->entityManager->getRepository(FootballCompetition::class)->find($competitionId);
        if ($competition === null) {
            throw new RuntimeException('Football competition not found.');
        }
        return $competition;
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return FootballCompetition|false|null
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function parseFootballCompetitionOption(InputInterface $input, string $option): FootballCompetition|false|null
    {
        $competitionId = $input->getOption($option);
        if ($competitionId === null || $competitionId === false) {
            return $competitionId;
        }
        if (!is_numeric($competitionId)) {
            throw new ValueError(sprintf('The %s option must be a numeric value.', $option));
        }
        $competition = $this->entityManager->find(FootballCompetition::class, $competitionId);
        if ($competition === null) {
            throw new RuntimeException('Football competition not found.');
        }
        return $competition;
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballTeam|null
     */
    protected function parseFootballTeamArgument(InputInterface $input, string $argument): ?FootballTeam
    {
        $teamId = $input->getArgument($argument);
        if ($teamId === null) {
            return null;
        }
        if (!is_numeric($teamId)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        $team = $this->entityManager->getRepository(FootballTeam::class)->find($teamId);
        if ($team === null) {
            throw new RuntimeException('Football team not found.');
        }
        return $team;
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return FootballCompetitionTeamEntry|null
     */
    protected function parseFootballCompetitionTeamEntryArgument(InputInterface $input, string $argument): ?FootballCompetitionTeamEntry
    {
        $entryId = $input->getArgument($argument);
        if ($entryId === null) {
            return null;
        }
        if (!is_numeric($entryId)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        $entry = $this->entityManager->getRepository(FootballCompetitionTeamEntry::class)->find($entryId);
        if ($entry === null) {
            throw new RuntimeException('Football competition team entry not found.');
        }
        return $entry;
    }
}

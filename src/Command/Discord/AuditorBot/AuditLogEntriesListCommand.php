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

namespace App\Command\Discord\AuditorBot;

use App\Domain\Discord\AuditorBot\AuditorBotAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:discord:auditor-bot:audit-log-entries:list',
    description: 'List discord guild audit log entries'
)]
final class AuditLogEntriesListCommand extends Command
{
    use AuditorBotAwareTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'guild-id',
                mode: InputArgument::REQUIRED,
                description: 'The discord guild id'
            )
            ->addOption(
                name: 'user-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Filter for entries from a specific discord user id'
            )
            ->addOption(
                name: 'action-type',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Filter for entries with a specific audit log event'
            )
            ->addOption(
                name: 'before',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Filter for entries with id less than a specific audit log entry id'
            )
            ->addOption(
                name: 'after',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Filter for entries with id greater than a specific audit log entry id'
            )
            ->addOption(
                name: 'limit',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Limit the number of entries displayed (1-100)'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to find and display
                discord audit log entries based on some criteria.

                Usage:
                  <info>%command.full_name% <guild-id>
                    [--user-id <user-id>] [--action-type <action-type>]
                    [--before <before>] [--after <after>]
                    [--limit <limit>]</info>

                Examples:
                  <info>%command.full_name% 1458864796240842875 --action-type 72</info>

                If no guild id is specified, you'll be prompted interactively.
                HELP
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getArgument('guild-id') === null) {
            $guildChoicesResolver = $this->auditorBot->getCurrentUserGuildsChoicesResolver();
            if ($guildChoicesResolver->hasChoices()) {
                $this->interactChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    'guild-id',
                    'Discord guild: ',
                    $guildChoicesResolver
                );
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Discord: Auditor Bot: List Audit Log Entries');

        try {
            $guildId = $input->getArgument('guild-id');
            if (!is_numeric($guildId)) {
                $io->error('Discord guild id must be numeric');
                return Command::FAILURE;
            }

            $userId = $input->getOption('user-id');
            if ($userId !== null && !is_numeric($userId)) {
                $io->error('Discord user id must be numeric');
                return Command::FAILURE;
            }

            $actionType = $input->getOption('action-type');
            if ($actionType !== null) {
                if (!is_numeric($actionType)) {
                    $io->error('Discord audit log event type must be numeric');
                    return Command::FAILURE;
                }
                $actionType = intval($actionType);
            }

            $before = $input->getOption('before');
            if ($before !== null && !is_numeric($before)) {
                $io->error('Discord audit log entry id must be numeric');
                return Command::FAILURE;
            }

            $after = $input->getOption('after');
            if ($after !== null && !is_numeric($after)) {
                $io->error('Discord audit log entry id must be numeric');
                return Command::FAILURE;
            }

            $limit = $input->getOption('limit');
            if ($limit !== null) {
                if (!is_numeric($limit)) {
                    $io->error('Limit must be numeric');
                    return Command::FAILURE;
                }
                $limit = intval($limit);
                if ($limit < 1 || $limit > 100) {
                    $io->error('Limit must be an integer between 1 and 100');
                    return Command::FAILURE;
                }
            }

            $guild = $this->auditorBot->getCurrentUserGuild($guildId);
            $entries = $this->auditorBot->getGuildAuditLogEntries($guildId, $userId, $actionType, $before, $after, $limit);
            foreach ($entries as $entry) {
                $io->definitionList(...$this->definitionListConverter->convert($entry));
            }
            $io->info(sprintf('Found %d audit log entries in %s.', count($entries), $guild['name']));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

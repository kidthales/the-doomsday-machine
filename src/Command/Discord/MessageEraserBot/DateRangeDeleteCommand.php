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

namespace App\Command\Discord\MessageEraserBot;

use App\Domain\Discord\MessageEraserBot\MessageEraserBotAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use App\Domain\Shared\Discord\DiscordApi;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Helper\TableSeparator;
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
    name: 'app:discord:message-eraser-bot:date-range-delete',
    description: 'Delete discord messages within guild channel, by date range'
)]
final class DateRangeDeleteCommand extends Command
{
    use MessageEraserBotAwareTrait;

    /**
     * @param string $dateString
     * @return DateTimeInterface|false
     */
    private static function parseDateString(string $dateString): DateTimeInterface|false
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateString);
        return $date ?: DateTimeImmutable::createFromFormat('Y-m-d|', $dateString);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'channel-id',
                mode: InputArgument::REQUIRED,
                description: 'The discord channel id'
            )
            ->addArgument(
                name: 'start-date',
                mode: InputArgument::REQUIRED,
                description: 'Delete discord messages with timestamp equal to or greater than start date'
            )
            ->addArgument(
                name: 'end-date',
                mode: InputArgument::REQUIRED,
                description: 'Delete discord messages with timestamp less than end date'
            )
            ->addOption(
                name: 'reason',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Include a brief plain text reason for the guild audit logs'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to delete
                discord messages, within a guild channel, by date range.

                Usage:
                  <info>%command.full_name% <channel-id> <start-date> <end-date> [--reason <reason>]</info>

                Examples:
                  <info>%command.full_name% 1459278103053471804 2026-02-01 2026-03-01 --reason "Monthly clean up"</info>

                If no channel id, start date, or end date is specified, you'll be prompted interactively.
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
        if ($input->getArgument('channel-id') === null) {
            $guildChoicesResolver = $this->messageEraserBot->getCurrentUserGuildsChoicesResolver();
            if ($guildChoicesResolver->hasChoices()) {
                $guildId = $this->askChoiceQuestionWithChoicesResolver(
                    $input,
                    $output,
                    'Discord guild: ',
                    $guildChoicesResolver
                );
                $channelChoicesResolver = $this->messageEraserBot->getGuildChannelsChoicesResolver($guildId);
                if ($channelChoicesResolver->hasChoices()) {
                    $this->interactChoiceQuestionWithChoicesResolver(
                        $input,
                        $output,
                        'channel-id',
                        'Discord channel: ',
                        $channelChoicesResolver
                    );
                }
            }
        }

        $this->interactQuestion($input, $output, 'start-date', 'Start date: ');
        $this->interactQuestion($input, $output, 'end-date', 'End date: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Discord: Message Eraser Bot Date Range Delete');

        try {
            $channelId = $input->getArgument('channel-id');
            if (!is_numeric($channelId)) {
                $io->error('Discord channel id must be numeric');
                return Command::FAILURE;
            }

            $startDate = self::parseDateString($input->getArgument('start-date'));
            if (!$startDate) {
                $io->error('Invalid start date');
                return Command::FAILURE;
            }

            $endDate = self::parseDateString($input->getArgument('end-date'));
            if (!$endDate) {
                $io->error('Invalid end date');
                return Command::FAILURE;
            }

            if ($startDate > $endDate) {
                $io->error('Start date must be before end date');
                return Command::FAILURE;
            }

            $channel = $this->messageEraserBot->getGuildChannel($channelId);
            $category = isset($channel['parent_id'])
                ? $this->messageEraserBot->getGuildChannel($channel['parent_id'])['name']
                : null;
            $guild = $this->messageEraserBot->getCurrentUserGuild($channel['guild_id']);
            $targetName = sprintf(
                '%s  >  %s',
                $guild['name'],
                sprintf('%s%s', $category === null ? '' : sprintf('%s  >  ', $category), $channel['name'])
            );
            $io->definitionList(
                $targetName,
                new TableSeparator(),
                ['Start Date' => $startDate->format('Y-m-d H:i:s')],
                ['End Date' => $endDate->format('Y-m-d H:i:s')]
            );

            $progressIndicator = new ProgressIndicator($output);
            $progressIndicator->start('Searching...');
            $messages = $this->messageEraserBot->getMessagesWithinDateRange(
                $channelId,
                $startDate,
                $endDate,
                $progressIndicator
            );
            $messagesCount = count($messages);
            $progressIndicator->finish(sprintf('Found %d messages.', $messagesCount));
            $io->newLine();

            if ($messagesCount === 0) {
                $io->success('No messages found.');
                return Command::SUCCESS;
            }

            $messageDeletionBucket = $this->messageEraserBot->bucketMessagesForDeletion($messages);
            $io->definitionList(
                'Message Deletion Bucket Counts',
                new TableSeparator(),
                ['Bulk' => $messageDeletionBucket->getBulkCount()],
                ['Individual' => $messageDeletionBucket->getIndividualCount()]
            );

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(['Reason' => $input->getOption('reason') ?? '']);
                $defaultTimeZone = new DateTimeZone(date_default_timezone_get());
                $messageLinkTemplate = 'https://discord.com/channels/%s/%s/%s';
                $oldestMessage = $messages[0];
                $oldestTimestamp = DiscordApi::parseDiscordTimestamp($oldestMessage['timestamp'])
                    ->setTimezone($defaultTimeZone)
                    ->format('Y-m-d H:i:s');
                $oldestLink = sprintf($messageLinkTemplate, $channel['guild_id'], $channelId, $oldestMessage['id']);
                $io->definitionList(
                    $messagesCount > 1 ? 'Oldest Message' : 'Message',
                    new TableSeparator(),
                    ['Timestamp' => $oldestTimestamp],
                    ['Link' => $oldestLink]
                );
                if ($messagesCount > 1) {
                    $newestMessage = $messages[$messagesCount - 1];
                    $newestTimestamp = DiscordApi::parseDiscordTimestamp($newestMessage['timestamp'])
                        ->setTimezone($defaultTimeZone)
                        ->format('Y-m-d H:i:s');
                    $newestLink = sprintf($messageLinkTemplate, $channel['guild_id'], $channelId, $newestMessage['id']);
                    $io->definitionList(
                        'Newest Message',
                        new TableSeparator(),
                        ['Timestamp' => $newestTimestamp],
                        ['Link' => $newestLink]
                    );
                }

                if (!$io->confirm(sprintf('Delete %d messages?', $messagesCount))) {
                    $io->success('Operation cancelled.');
                    return Command::SUCCESS;
                }
            }

            $progressBar = $io->createProgressBar($messagesCount);
            $progressBar->start();
            try {
                $this->messageEraserBot->deleteMessages(
                    channelId: $channelId,
                    bucket: $messageDeletionBucket,
                    reason: $input->getOption('reason'),
                    progressBar: $progressBar
                );
            } finally {
                $progressBar->finish();
                $io->newLine(2);
            }

            $io->success(sprintf('Deleted %d messages.', $messagesCount));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

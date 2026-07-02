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

namespace App\Domain\Discord\MessageEraserBot;

use App\Domain\Shared\Discord\DiscordApi;
use App\Domain\Shared\Discord\DiscordBot;
use DateTimeImmutable;
use DateTimeInterface;
use Godruoyi\Snowflake\SnowflakeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class MessageEraserBot extends DiscordBot
{
    // Get Channel Messages: VIEW_CHANNEL, CONNECT (voice), READ_MESSAGE_HISTORY
    // Delete Message: MANAGE_MESSAGES
    // Bulk Delete Messages: MANAGE_MESSAGES
    // Delete Thread: MANAGE_THREADS
    protected const int DISCORD_PERMISSIONS = 17180992512;

    /**
     * @param string $token
     */
    public function __construct(#[Autowire(env: 'string:DISCORD_MESSAGE_ERASER_BOT_TOKEN')] string $token)
    {
        parent::__construct($token);
    }

    /**
     * @param string $channelId
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @param ProgressIndicator|null $progressIndicator
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SnowflakeException
     * @throws TransportExceptionInterface
     */
    public function getMessagesWithinDateRange(
        string $channelId,
        DateTimeInterface $start,
        DateTimeInterface $end,
        ?ProgressIndicator $progressIndicator = null,
    ): array
    {
        $limit = 100;
        $messages = [];
        $process = function (array $candidates) use ($start, $end, &$messages, $progressIndicator) {
            for ($i = count($candidates) - 1; $i >= 0; --$i) {
                $date = DiscordApi::parseDiscordTimestamp($candidates[$i]['timestamp']);
                if ($date >= $start && $date < $end) {
                    $messages[] = $candidates[$i];
                }
                $progressIndicator?->advance();
            }
        };

        $candidates =  $this->discordApi->getChannelMessages(
            channelId: $channelId,
            around: $this->createSnowflake()->idForTimestamp($start),
            limit: $limit
        )->toArray();

        if (!empty($candidates)) {
            $process($candidates);

            while(DiscordApi::parseDiscordTimestamp($candidates[0]['timestamp']) < $end) {
                $candidates = $this->discordApi->getChannelMessages(
                    channelId: $channelId,
                    after: $candidates[0]['id'],
                    limit: $limit
                )->toArray();

                if (empty($candidates)) {
                    break;
                }

                $process($candidates);
            }
        }

        return $messages;
    }

    /**
     * @param array $messages
     * @return MessageDeletionBucket
     */
    public function bucketMessagesForDeletion(array $messages): MessageDeletionBucket
    {
        $twoWeeksAgo = new DateTimeImmutable('-2 weeks');
        $bulk = [];
        $individual = [];

        foreach ($messages as $message) {
            if (DiscordApi::parseDiscordTimestamp($message['timestamp']) >= $twoWeeksAgo) {
                $bulk[] = $message;
            } else {
                $individual[] = $message;
            }
        }

        $bulkCount = count($bulk);
        if ($bulkCount < 2) {
            $individual = [...$bulk, ...$individual];
            $bulk = [];
        } else {
            $bulk = array_chunk($bulk,(int)ceil($bulkCount / ceil($bulkCount / 100)));
        }

        return new MessageDeletionBucket($bulk, $individual);
    }

    /**
     * @param string $channelId
     * @param MessageDeletionBucket $bucket
     * @param string|null $reason
     * @param ProgressBar|null $progressBar
     * @return void
     */
    public function deleteMessages(
        string $channelId,
        MessageDeletionBucket $bucket,
        ?string $reason = null,
        ?ProgressBar $progressBar = null
    ): void
    {
        $deletionCount = 0;
        try {
            $reason = sprintf('[%s] %s', $this->getCurrentApplication()['name'], $reason ?? 'Unspecified');
            foreach ($bucket->bulk as $batch) {
                $response = $this->discordApi->bulkDeleteMessages($channelId, array_map(fn(array $m) => $m['id'], $batch), $reason);
                if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
                    $batchCount = count($batch);
                    $deletionCount += $batchCount;
                    $progressBar?->advance($batchCount);
                } else {
                    // Throw the exception
                    $response->toArray();
                }
            }
            foreach ($bucket->individual as $individual) {
                $response = $this->discordApi->deleteMessage($channelId, $individual['id'], $reason);
                if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
                    ++$deletionCount;
                    $progressBar?->advance();
                } else {
                    // Throw the exception
                    $response->toArray();
                }
            }
        } catch (Throwable $e) {
            throw new MessageDeletionException($deletionCount, $e);
        }
    }
}

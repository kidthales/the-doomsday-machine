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

namespace App\Domain\Shared\Discord;

use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use ValueError;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class DiscordApiRetryStrategy extends GenericRetryStrategy
{
    /**
     * @param AsyncContext $context
     * @param string|null $responseContent
     * @param TransportExceptionInterface|null $exception
     * @return int
     */
    public function getDelay(AsyncContext $context, ?string $responseContent, ?TransportExceptionInterface $exception): int
    {
        if ($context->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS) {
            $retryAfter = $context->getHeaders()['Retry-After'];
            if ($retryAfter === null) {
                $content = json_decode($responseContent ?? '{}', true);
                $retryAfter = $content['retry_after'] ?? null;
            }
            if (!is_numeric($retryAfter)) {
                // TODO: Update retry after handling if the api also gives us date time strings...
                throw new ValueError(sprintf('Expected a numeric retry after value, got "%s"', $retryAfter));
            }
            return intval($retryAfter) + 1;
        }

        return parent::getDelay($context, $responseContent, $exception);
    }
}

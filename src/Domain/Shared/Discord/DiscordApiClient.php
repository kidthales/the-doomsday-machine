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

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class DiscordApiClient
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    /**
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://discord.com/developers/docs/resources/application#get-current-application
     */
    public function getCurrentApplication(): ResponseInterface
    {
        return $this->request('GET', 'applications/@me');
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    private function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, filter_var($url, FILTER_SANITIZE_URL), $options);
    }
}

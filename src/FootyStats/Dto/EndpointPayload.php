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

namespace App\FootyStats\Dto;

use Symfony\Component\DomCrawler\Crawler;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class EndpointPayload
{
    /**
     * @param Crawler $node
     * @return self
     */
    public static function fromNode(Crawler $node): self
    {
        return new self(
            hash: $node->attr('data-hash'),
            z: $node->attr('data-z'),
            zzz: $node->attr('data-zzz'),
            zzzz: $node->attr('data-zzzz')
        );
    }

    /**
     * @param EndpointPayload $payload
     * @return array
     */
    public static function toRequestBody(EndpointPayload $payload): array
    {
        return ['hash' => $payload->hash, 'cur' => $payload->z, 'zzz' => $payload->zzz, 'zzzz' => $payload->zzzz];
    }

    /**
     * @param mixed|null $hash
     * @param mixed|null $z
     * @param mixed|null $zzz
     * @param mixed|null $zzzz
     */
    public function __construct(
        public mixed $hash = null,
        public mixed $z = null,
        public mixed $zzz = null,
        public mixed $zzzz = null
    )
    {
    }
}

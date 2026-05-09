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

namespace App\Domain\Shared\AI;

use RuntimeException;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\StreamResult;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class PlatformStreamResultProcessor
{
    /**
     * @param StreamResult $result
     * @param (callable(StreamResult):void)|null $onStart
     * @param (callable(TextDelta):void)|null $textDeltaProcessor
     * @param (callable(ThinkingDelta):void)|null $thinkingDeltaProcessor
     * @param (callable(StreamResult):void)|null $onFinish
     * @return void
     */
    public function process(
        StreamResult $result,
        ?callable    $onStart = null,
        ?callable    $textDeltaProcessor = null,
        ?callable    $thinkingDeltaProcessor = null,
        ?callable    $onFinish = null
    ): void
    {
        if (\is_callable($onStart)) {
            $onStart($result);
        }

        foreach ($result->getContent() as $delta) {
            if ($delta instanceof TextDelta) {
                if (\is_callable($textDeltaProcessor)) {
                    $textDeltaProcessor($delta);
                }
            } else if ($delta instanceof ThinkingDelta) {
                if (\is_callable($thinkingDeltaProcessor)) {
                    $thinkingDeltaProcessor($delta);
                }
            } else {
                throw new RuntimeException('Unexpected stream result content delta type');
            }
        }

        if (\is_callable($onFinish)) {
            $onFinish($result);
        }
    }
}

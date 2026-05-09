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

namespace App\Domain\Shared\String;

use InvalidArgumentException;
use function Symfony\Component\String\u;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class TagSearch
{
    private const int STATE_READY = 0;
    private const int STATE_WORD = 1;
    private const int STATE_TAG_CANDIDATE = 2;
    private const int STATE_TAG = 3;
    private const int STATE_TAG_ESCAPE_WHITESPACE_CANDIDATE = 4;

    /**
     * @param string $subject
     * @param string $start
     * @return TagSearchResult[]
     */
    public function search(string $subject, string $start): array
    {
        if (u($start)->length() !== 1 || preg_match('/^\s$/', $subject) === 1) {
            throw new InvalidArgumentException('Tag start string must have exactly 1 non-whitespace character');
        }

        $state = self::STATE_READY;
        $tags = [];
        $tag = '';
        foreach (u($subject)->chunk() as $chunk) {
            $ch = $chunk->toString();

            switch ($state) {
                case self::STATE_READY:
                    if ($ch === $start) {
                        $tag = $ch;
                        $state = self::STATE_TAG_CANDIDATE;
                    } else if (preg_match('/^\S$/', $ch)) {
                        $state = self::STATE_WORD;
                    }
                    break;
                case self::STATE_WORD:
                    if (preg_match('/^\s$/', $ch)) {
                        $state = self::STATE_READY;
                    }
                    break;
                case self::STATE_TAG_CANDIDATE:
                    if (preg_match('/^\S$/', $ch)) {
                        $tag .= $ch;
                        $state = self::STATE_TAG;
                    } else {
                        $tag = '';
                        $state = self::STATE_READY;
                    }
                    break;
                case self::STATE_TAG:
                    if ($ch === '\\') {
                        $tag .= $ch;
                        $state = self::STATE_TAG_ESCAPE_WHITESPACE_CANDIDATE;
                    } else if (preg_match('/^\s$/', $ch)) {
                        $tags[] = $tag;
                        $tag = '';
                        $state = self::STATE_READY;
                    } else {
                        $tag .= $ch;
                    }
                    break;
                case self::STATE_TAG_ESCAPE_WHITESPACE_CANDIDATE:
                    $tag .= $ch;
                    if ($ch !== '\\') {
                        $state = self::STATE_TAG;
                    }
                    break;
            }
        }

        switch ($state) {
            case self::STATE_TAG:
            case self::STATE_TAG_ESCAPE_WHITESPACE_CANDIDATE:
                $tags[] = $tag;
                break;
        }

        return array_map(fn($tag) => new TagSearchResult($tag), $tags);
    }
}

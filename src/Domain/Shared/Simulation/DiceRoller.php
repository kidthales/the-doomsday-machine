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

namespace App\Domain\Shared\Simulation;

use App\Domain\Shared\Validator\DiceRollFormat;
use Random\Randomizer;
use Symfony\Component\DependencyInjection\Attribute\AutowireInline;
use ValueError;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class DiceRoller
{
    public const string DICE_ROLL_FORMAT = '𝑋d𝐘[±𝐂]|𝑋∈ℤ,𝑋>0,𝐘∈ℤ,𝐘>1,𝐂∈ℤ,𝐂>0';
    public const string DICE_ROLL_FORMAT_REGEX = '/([1-9]\d*)d([2-9]\d*)(?:([+\-])([1-9]\d*))?/';

    /**
     * @param Randomizer $randomizer
     */
    public function __construct(#[AutowireInline(class: Randomizer::class)] private Randomizer $randomizer)
    {
    }

    /**
     * @param string $formula
     * @return DiceRollerResult
     */
    public function rollFormula(string $formula): DiceRollerResult
    {
        if (preg_match(self::DICE_ROLL_FORMAT_REGEX, $formula, $matches)) {
            switch (count($matches)) {
                case 3:
                    return $this->doRoll(intval($matches[1]), intval($matches[2]), 0);
                case 5:
                    $sign = $matches[3] === '+' ? 1 : -1;
                    return $this->doRoll(intval($matches[1]), intval($matches[2]), $sign * intval($matches[4]));
                default:
                    break;
            }
        }
        throw new ValueError(
            str_replace(
                ['{{ string }}', '{{ format }}'],
                [$formula, self::DICE_ROLL_FORMAT],
                (new DiceRollFormat())->message
            )
        );
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $c
     * @return DiceRollerResult
     */
    public function rollComponents(int $x, int $y, int $c = 0): DiceRollerResult
    {
        if ($x < 1 || $y < 2) {
            throw new ValueError(
                sprintf('Dice roll components must correspond to the dice roll format %s', self::DICE_ROLL_FORMAT)
            );
        }
        return $this->doRoll($x, $y, $c);
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $c
     * @return DiceRollerResult
     */
    public function doRoll(int $x, int $y, int $c): DiceRollerResult
    {
        $result = 0;
        $details = [];
        for ($i = 1; $i <= $x; ++$x) {
            $details[] = $this->randomizer->getInt(1, $y);
            $result += $details[$i - 1];
        }
        $result += $c;
        return new DiceRollerResult($result, $details, $c);
    }
}

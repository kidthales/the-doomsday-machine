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

namespace App\Domain\Jabronibetz\Calculator;

use Symfony\Contracts\Service\Attribute\Required;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait FootballMatchTeamReferenceFrameAggregationCalculatorAwareTrait
{
    /**
     * @var FootballMatchTeamReferenceFrameAggregationCalculator|null
     */
    protected ?FootballMatchTeamReferenceFrameAggregationCalculator $footballMatchTeamReferenceFrameAggregationCalculator = null;

    /**
     * @return FootballMatchTeamReferenceFrameAggregationCalculator|null
     */
    public function getFootballMatchTeamReferenceFrameAggregationCalculator(): ?FootballMatchTeamReferenceFrameAggregationCalculator
    {
        return $this->footballMatchTeamReferenceFrameAggregationCalculator;
    }

    /**
     * @param FootballMatchTeamReferenceFrameAggregationCalculator $calculator
     * @return $this
     */
    #[Required]
    public function setFootballMatchTeamReferenceFrameAggregationCalculator(FootballMatchTeamReferenceFrameAggregationCalculator $calculator): static
    {
        $this->footballMatchTeamReferenceFrameAggregationCalculator = $calculator;
        return $this;
    }
}

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

namespace App\FootyStats\Database;

use App\FootyStats\Target;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Autoconfigure(public: true)]
final readonly class TeamStrengthView extends AbstractView
{
    public const string BASE_NAME = 'team_strength';

    public static function getCreateSql(Target $target): string
    {
        $sql = <<<'SQL'
            CREATE VIEW <view_name> AS
            SELECT team_name,
                   goals_for_per_game / (SELECT AVG(goals_for_per_game) FROM <team_standing_view_name>)         AS attack,
                   goals_against_per_game / (SELECT AVG(goals_against_per_game) FROM <team_standing_view_name>) AS defense
            FROM <team_standing_view_name>
            ORDER BY team_name;
SQL;

        return s($sql)
            ->replace('<view_name>', self::getName($target))
            ->replace('<team_standing_view_name>', TeamStandingView::getName($target))
            ->toString();
    }

    public function __construct(#[Autowire(service: 'doctrine.dbal.footy_stats_connection')] Connection $connection)
    {
        parent::__construct($connection);
    }
}

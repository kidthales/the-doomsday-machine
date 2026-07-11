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

namespace App\Command;

use App\Domain\Shared\Console\Command\Command;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:sqlite:vacuum',
    description: 'Vacuum a sqlite database to file'
)]
final class SQLiteVacuumCommand extends Command
{
    /**
     * @param ManagerRegistry $doctrineManagerRegistry
     */
    public function __construct(private readonly ManagerRegistry $doctrineManagerRegistry)
    {
        parent::__construct();
    }

    /**
     * @param CompletionInput $input
     * @param CompletionSuggestions $suggestions
     * @return void
     */
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('connection')) {
            $suggestions->suggestValues($this->getAvailableConnectionNames());
        }
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'connection',
                mode: InputArgument::REQUIRED,
                description: 'The name of the sqlite connection'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to vacuum a sqlite database to a file.

                Usage:
                  <info>%command.full_name% <connection></info>

                Examples:
                  <info>%command.full_name% default</info>

                If no connection is specified, you'll be prompted to select one interactively.
                HELP
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $availableConnections = $this->getAvailableConnectionNames();
        if (!empty($availableConnections)) {
            $this->interactChoiceQuestion($input, $output, 'connection', 'Choose a connection: ', $availableConnections);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Vacuum SQLite Database');

        try {
            $connectionName = $this->parseStringArgument($input, 'connection', true);
            /** @var Connection $connection */
            $connection = $this->doctrineManagerRegistry->getConnection($connectionName);
            $path = sprintf(
                '%s~%s',
                $connection->getParams()['path'],
                (new \DateTimeImmutable('now'))->format('Y_m_d_H_i_s')
            );
            $connection->executeStatement('VACUUM INTO ?', [$path]);
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('Completed vacuum into %s using %s connection.', $path, $connectionName));
        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getAvailableConnectionNames(): array
    {
        $availableConnections = [];
        foreach (array_keys($this->doctrineManagerRegistry->getConnectionNames()) as $connectionName) {
            /** @var Connection $connection */
            $connection = $this->doctrineManagerRegistry->getConnection($connectionName);
            if ($connection->getParams()['driver'] === 'pdo_sqlite') {
                $availableConnections[] = $connectionName;
            }
        }
        return $availableConnections;
    }
}

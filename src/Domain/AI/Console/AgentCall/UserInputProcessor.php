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

namespace App\Domain\AI\Console\AgentCall;

use App\Domain\AI\Console\AgentCall\UserInput\ChatUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ErrorUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ExitUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\NoopUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\UserInput;
use App\Domain\Shared\String\TagSearch;
use Symfony\AI\Platform\Message\Content\Audio;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class UserInputProcessor
{
    private const int MAX_ATTACHMENT_SIZE = 1024 * 1024 * 10; // 10 MB

    /**
     * @param TagSearch $tagSearch
     * @param string $projectDir
     */
    public function __construct(
        private TagSearch                                       $tagSearch,
        #[Autowire(param: 'kernel.project_dir')] private string $projectDir,
    )
    {
    }

    /**
     * @param mixed $rawUserInput
     * @param SymfonyStyle $io
     * @return UserInput
     */
    public function process(mixed $rawUserInput, SymfonyStyle $io): UserInput
    {
        if (!is_string($rawUserInput) || empty(trim($rawUserInput))) {
            return new NoopUserInput();
        }

        $userInput = u($rawUserInput);

        $matches = $userInput->match('/^\/([A-Za-z0-9]\w*)(:?\s*)(.*)/s');
        if (count($matches) > 1) {
            return $this->processSlashCommandInput($matches[1], $matches[2] ?? '');
        }

        return $this->processChatInput($userInput, $io);
    }

    /**
     * @param string $slashCommand
     * @param string $slashCommandRawInput
     * @return UserInput
     */
    private function processSlashCommandInput(string $slashCommand, string $slashCommandRawInput): UserInput
    {
        return match ($slashCommand) {
            'exit', 'quit', 'bye' => new ExitUserInput(),
            default => new ErrorUserInput(sprintf('Unknown slash command "%s"', $slashCommand))
        };
    }

    /**
     * @param UnicodeString $userInput
     * @param SymfonyStyle $io
     * @return UserInput
     */
    private function processChatInput(UnicodeString $userInput, SymfonyStyle $io): UserInput
    {
        $messages = new MessageBag();
        $attachmentTable = [];

        foreach ($this->tagSearch->search($userInput->toString(), '@') as $tagSearchResult) {
            // Canonicalized
            $path = Path::makeAbsolute($tagSearchResult->subject, $this->projectDir);
            $row = [$tagSearchResult->tag, $tagSearchResult->subject, $path];

            // TODO: file exclusion list...
            if (!str_starts_with($path, $this->projectDir . DIRECTORY_SEPARATOR)) {
                $row[] = '❌ (Access Denied)';
            } else if (!is_readable($path) || !is_file($path)) {
                $row[] = '❌ (Not Readable File)';
            } else if (filesize($path) > self::MAX_ATTACHMENT_SIZE) {
                $row[] = '❌ (Too Large)';
            } else {
                $mimeType = (new MimeTypes())->guessMimeType($path);

                if (str_starts_with($mimeType, 'image/')) {
                    $messages->add(Message::ofUser(Image::fromFile($path)));
                    $row[] = '❓ (Pending)';
                } else if (str_starts_with($mimeType, 'audio/')) {
                    $messages->add(Message::ofUser(Audio::fromFile($path)));
                    $row[] = '❓ (Pending)';
                } else if (str_starts_with($mimeType, 'text/')) {
                    $contents = file_get_contents($path);
                    $messages->add(
                        Message::ofUser(<<<MD
                        # $path
                        ```
                        $contents
                        ```
                        MD
                        )
                    );
                    $row[] = '✅';
                } else {
                    $row[] = '❌ (Unsupported MIME Type)';
                }
            }

            $attachmentTable[] = $row;
            $userInput = $userInput->replace($tagSearchResult->tag, '`' . $path . '`');
        }

        $io->writeln('<fg=cyan>' . $userInput . '</>');
        $io->newLine();

        if (!empty($attachmentTable)) {
            $io->table(['Tag', 'Subject', 'Path', 'Attached'], $attachmentTable);
        }

        $messages->add(Message::ofUser($userInput));

        return new ChatUserInput($messages);
    }
}

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

namespace App\Domain\AI\Console;

use App\Domain\Shared\AI\PlatformResultProcessor;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class AgentCallPlatformResultProcessor
{
    /**
     * @var bool
     */
    private bool $isThinking = false;

    /**
     * @var string
     */
    private string $textBuffer = '';

    /**
     * @var SymfonyStyle|null
     */
    private SymfonyStyle|null $io = null;

    /**
     * @var MessageBag|null
     */
    private MessageBag|null $messages = null;

    /**
     * @param PlatformResultProcessor $platformResultProcessor
     */
    public function __construct(private readonly PlatformResultProcessor $platformResultProcessor)
    {
    }

    /**
     * @param ResultInterface $result
     * @param SymfonyStyle $io
     * @param MessageBag $messages
     * @return void
     */
    public function process(ResultInterface $result, SymfonyStyle $io, MessageBag $messages): void
    {
        $this->io = $io;
        $this->messages = $messages;

        $this->platformResultProcessor->process(
            $result,
            textResultProcessor: fn (TextResult $r) => $this->processTextResult($r),
            onStreamResultStart: fn () => $this->writeStartText(),
            textDeltaProcessor: fn (TextDelta $d) => $this->processTextDelta($d),
            thinkingDeltaProcessor: fn (ThinkingDelta $d) => $this->processThinkingDelta($d),
            onStreamResultFinish: fn () => $this->finishProcess($this->textBuffer)
        );
    }

    /**
     * @param TextResult $result
     * @return void
     */
    private function processTextResult(TextResult $result): void
    {
        $this->writeStartText();
        $this->io?->writeln($result->getContent());
        $this->finishProcess($result->getContent());
    }

    /**
     * @return void
     */
    private function writeStartText(): void
    {
        $this->io?->writeln('<fg=yellow>Assistant</>:');
    }

    /**
     * @param TextDelta $delta
     * @return void
     */
    private function processTextDelta(TextDelta $delta): void
    {
        if ($this->isThinking) {
            $this->isThinking = false;
            $this->io?->write('</>');
            $this->io?->newLine();
        }

        $this->io?->write($delta->getText());
        $this->textBuffer .= $delta->getText();
    }

    /**
     * @param ThinkingDelta $delta
     * @return void
     */
    private function processThinkingDelta(ThinkingDelta $delta): void
    {
        if (!$this->isThinking) {
            $this->isThinking = true;
            $this->io?->write('<fg=magenta>');
        }

        $this->io?->write($delta->getThinking());
    }

    /**
     * @param string $content
     * @return void
     */
    private function finishProcess(string $content): void
    {
        $this->io?->newLine();
        $this->messages?->add(Message::ofAssistant($content));

        $this->isThinking = false;
        $this->textBuffer = '';

        $this->io = null;
        $this->messages = null;
    }
}

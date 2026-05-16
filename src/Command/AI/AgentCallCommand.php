<?php
/*
 * Derived from https://github.com/symfony/ai/blob/4de652b6d2d3b9c86c7da0ee41d0de4ef0972497/src/ai-bundle/src/Command/AgentCallCommand.php
 * which is a file that is part of the Symfony package.
 *
 * Copyright (c) 2025-present Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

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

namespace App\Command\AI;

use App\Domain\AI\Console\AgentCall\PlatformResultProcessor;
use App\Domain\AI\Console\AgentCall\UserInput\ChatUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ErrorUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\ExitUserInput;
use App\Domain\AI\Console\AgentCall\UserInput\NoopUserInput;
use App\Domain\AI\Console\AgentCall\UserInputProcessor;
use InvalidArgumentException;
use RuntimeException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Agent\Exception\ExceptionInterface as AgentExceptionInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:ai:agent:call',
    description: 'Call an agent',
    aliases: ['app:ai:chat'],
)]
final class AgentCallCommand extends Command
{
    /**
     * @param ServiceLocator $agents
     * @param UserInputProcessor $userInputProcessor
     * @param PlatformResultProcessor $platformResultProcessor
     */
    public function __construct(
        #[AutowireLocator('ai.agent', 'name')] private readonly ServiceLocator $agents,
        private readonly UserInputProcessor                                    $userInputProcessor,
        private readonly PlatformResultProcessor                               $platformResultProcessor
    )
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
        if ($input->mustSuggestArgumentValuesFor('agent')) {
            $suggestions->suggestValues($this->getAvailableAgentNames());
        }
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument('agent', InputArgument::REQUIRED, 'The name of the agent to chat with')
            ->addOption('no-stream', mode: InputOption::VALUE_NONE, description: 'Do not stream chat results')
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to chat with different agents.

                Usage:
                  <info>%command.full_name% [<agent_name>] [--no-stream]</info>

                Examples:
                  <info>%command.full_name% wikipedia</info>

                If no agent is specified, you'll be prompted to select one interactively.

                The chat session is interactive. Type your message and press <comment>Ctrl+D</comment> to send.
                Type <comment>/exit</comment>, <comment>/quit</comment>, or <comment>/bye</comment> to end the conversation.

                Files may be attached to a message by typing <comment>@</comment> followed by a path to the file
                (relative to the project's root directory). The path string is terminated at the
                first non-escaped whitespace encountered.

                Results are streamed by default. For non-streaming results, use the --no-stream
                option.
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
        if ($input->getArgument('agent') !== null) {
            return;
        }

        $availableAgents = $this->getAvailableAgentNames();
        if (0 === count($availableAgents)) {
            throw new InvalidArgumentException('No agents are configured.');
        }

        $question = new ChoiceQuestion(
            'Please select an agent to chat with:',
            $availableAgents,
            0
        );
        $question->setErrorMessage('Agent %s is invalid.');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $selectedAgent = $helper->ask($input, $output, $question);
        $input->setArgument('agent', $selectedAgent);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $agent = $this->resolveAgentInput($input);

        $io->title(sprintf('Chat with %s Agent', $agent->getName()));
        $io->info('Type your message and press "Ctrl+D". Type "/exit", "/quit", or "/bye" to end the conversation.');
        $io->newLine();

        $messages = new MessageBag();
        $systemPromptDisplayed = false;

        while (true) {
            $userInput = $this->userInputProcessor->process(
                $io->askQuestion((new Question('You'))->setMultiline(true)),
                $io
            );

            switch (get_class($userInput)) {
                case ExitUserInput::class:
                    break 2;
                case ChatUserInput::class:
                    $messages = $messages->merge($userInput->messages);
                    break;
                case ErrorUserInput::class:
                    $io->error($userInput->message); // fall-through
                case NoopUserInput::class:
                default:
                    continue 2;
            }

            try {
                $result = $agent->call($messages, ['stream' => !$input->getOption('no-stream')]);
            } catch (AgentExceptionInterface $e) {
                $io->error(sprintf('Agent call failed: %s', $e->getMessage()));
                continue;
            }

            if (!$systemPromptDisplayed && null !== ($systemMessage = $messages->getSystemMessage())) {
                $io->section('System Prompt');
                $io->block($systemMessage->getContent(), null, 'fg=gray', ' ', true);
                $systemPromptDisplayed = true;
            }

            $this->platformResultProcessor->process($result, $io, $messages);
        }

        $io->success('Goodbye!');

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getAvailableAgentNames(): array
    {
        return array_keys($this->agents->getProvidedServices());
    }

    /**
     * @param InputInterface $input
     * @return AgentInterface
     */
    private function resolveAgentInput(InputInterface $input): AgentInterface
    {
        $availableAgents = array_keys($this->agents->getProvidedServices());

        if (0 === count($availableAgents)) {
            throw new InvalidArgumentException('No agents are configured.');
        }

        $agentArg = $input->getArgument('agent');
        $agentName = is_string($agentArg) ? $agentArg : '';

        if ($agentName && !$this->agents->has($agentName)) {
            throw new InvalidArgumentException(sprintf(
                'Agent "%s" not found. Available agents: "%s"',
                $agentName, implode(', ', $availableAgents)
            ));
        }

        if (!$agentName) {
            throw new InvalidArgumentException(sprintf(
                'Agent name is required. Available agents: "%s"',
                implode(', ', $availableAgents)
            ));
        }

        return $this->agents->get($agentName);
    }
}

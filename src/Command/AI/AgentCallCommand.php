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

declare(strict_types=1);

namespace App\Command\AI;

use App\Domain\Shared\AI\PlatformResultProcessor;
use InvalidArgumentException;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\TextResult;
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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
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
     * @param ServiceLocator<AgentInterface> $agents
     */
    public function __construct(
        #[TaggedLocator('ai.agent', 'name')] private readonly ServiceLocator $agents,
        private readonly PlatformResultProcessor                             $platformResultProcessor,
        #[Autowire(param: 'kernel.project_dir')] private readonly string     $projectDir
    )
    {
        parent::__construct();
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor('agent')) {
            $suggestions->suggestValues($this->getAvailableAgentNames());
        }
    }

    protected function configure(): void
    {
        $this
            ->addArgument('agent', InputArgument::REQUIRED, 'The name of the agent to chat with')
            ->addOption('no-stream', mode: InputOption::VALUE_NONE, description: 'Do not stream chat results')
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to chat with different agents.

                Usage:
                  <info>%command.full_name% [<agent_name>]</info>

                Examples:
                  <info>%command.full_name% wikipedia</info>

                If no agent is specified, you'll be prompted to select one interactively.

                The chat session is interactive. Type your messages and press Enter to send.
                Type 'exit' or 'quit' to end the conversation.
                HELP
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $agentArg = $input->getArgument('agent');

        if ($agentArg) {
            return;
        }

        $availableAgents = $this->getAvailableAgentNames();

        if (0 === \count($availableAgents)) {
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $availableAgents = array_keys($this->agents->getProvidedServices());

        if (0 === \count($availableAgents)) {
            throw new InvalidArgumentException('No agents are configured.');
        }

        $agentArg = $input->getArgument('agent');
        $agentName = \is_string($agentArg) ? $agentArg : '';

        if ($agentName && !$this->agents->has($agentName)) {
            throw new InvalidArgumentException(\sprintf('Agent "%s" not found. Available agents: "%s"', $agentName, implode(', ', $availableAgents)));
        }

        if (!$agentName) {
            throw new InvalidArgumentException(\sprintf('Agent name is required. Available agents: "%s"', implode(', ', $availableAgents)));
        }

        $agent = $this->agents->get($agentName);

        $io = new SymfonyStyle($input, $output);

        $io->title(\sprintf('Chat with %s Agent', $agentName));
        $io->info('Type your message and press Enter. Type "exit" or "quit" to end the conversation.');
        $io->newLine();

        $messages = new MessageBag();
        $systemPromptDisplayed = false;

        while (true) {
            $userInput = $io->ask('You');

            if (!\is_string($userInput) || '' === trim($userInput)) {
                continue;
            }

            if (\in_array(strtolower($userInput), ['exit', 'quit'], true)) {
                $io->success('Goodbye!');
                break;
            }

            $paths = [];
            preg_match_all('/@([\'"])((?:(?!\1)[^\\\\]|\\\\.)*)\1|@(\S+)/', $userInput, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (!empty($match[2])) {
                    $paths[] = stripslashes($match[2]);
                } else if (!empty($match[3])) {
                    $paths[] = $match[3];
                }
                $userInput = str_replace($match[0], $paths[count($paths) - 1], $userInput);
            }
            $io->writeln('<fg=cyan>' . $userInput . '</>');
            $messages = $messages->merge($this->getFileMessages($paths));

            $messages->add(Message::ofUser($userInput));

            $options = ['stream' => !$input->getOption('no-stream')];

            $result = $agent->call($messages, $options);

            if (!$systemPromptDisplayed && null !== ($systemMessage = $messages->getSystemMessage())) {
                $io->section('System Prompt');
                $io->block($systemMessage->getContent(), null, 'fg=gray', ' ', true);
                $systemPromptDisplayed = true;
            }

            $isThinking = false;
            $textBuffer = '';
            $this->platformResultProcessor->process(
                $result,
                textResultProcessor: function (TextResult $result) use ($io, $messages) {
                    $io->writeln('<fg=yellow>Assistant</>:');
                    $io->writeln($result->getContent());
                    $io->newLine();

                    $messages->add(Message::ofAssistant($result->getContent()));
                },
                onStreamResultStart: function () use ($io) {
                    $io->writeln('<fg=yellow>Assistant</>:');
                },
                textDeltaProcessor: function (TextDelta $delta) use ($io, &$isThinking, &$textBuffer) {
                    if ($isThinking) {
                        $isThinking = false;
                        $io->write('</>');
                        $io->newLine();
                    }
                    $text = $delta->getText();
                    $io->write($text);
                    $textBuffer .= $text;
                },
                thinkingDeltaProcessor: function (ThinkingDelta $delta) use ($io, &$isThinking) {
                    if (!$isThinking) {
                        $isThinking = true;
                        $io->write('<fg=magenta>');
                    }
                    $io->write($delta->getThinking());
                },
                onStreamResultFinish: function () use ($io, $messages, $textBuffer) {
                    $io->newLine();
                    $messages->add(Message::ofAssistant($textBuffer));
                }
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getAvailableAgentNames(): array
    {
        return array_keys($this->agents->getProvidedServices());
    }

    private function getFileMessages(array $paths): MessageBag
    {
        $messages = new MessageBag();
        foreach ($paths as $path) {
            $contents = file_get_contents($path);
            $message = <<<MD
# $path
```
{$contents}
```
MD;
            $messages->add(Message::ofUser($message));
        }

        return $messages;
    }
}

<?php

namespace App\Command\AI;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:ai:test')]
final class TestCommand extends Command
{
    public function __construct(private readonly AgentInterface $agent)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $messages = new MessageBag(
            Message::forSystem('Your are being tested with Symfony AI.'),
            Message::ofUser('The "Riddle of Steel". Do you know it, boy?')
        );

        $result = $this->agent->call($messages);

        $output->write($result->getContent());
        $output->writeln('');

        return Command::SUCCESS;
    }
}

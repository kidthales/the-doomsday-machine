<?php

declare(strict_types=1);

namespace App\Command\Discord;

use App\Console\Style\DefinitionListConverterAwareInterface;
use App\Console\Style\DefinitionListConverterAwareTrait;
use App\Discord\ApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:discord:get-current-application',
    description: 'Get the current application from Discord API'
)]
final class GetCurrentApplicationCommand extends Command implements DefinitionListConverterAwareInterface
{
    use DefinitionListConverterAwareTrait;

    public function __construct(private readonly ApiClient $apiClient)
    {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Discord: Get Current Application');
        $io->definitionList(...$this->definitionListConverter->convert($this->apiClient->getCurrentApplication()));

        return Command::SUCCESS;
    }
}

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

namespace App\Domain\Shared\Console\Command;

use App\Domain\Shared\Console\Question\ChoicesBuilderAwareTrait;
use App\Domain\Shared\Console\Question\ChoicesResolver;
use App\Domain\Shared\Console\Question\ChoosableInterface;
use App\Domain\Shared\Console\Style\DefinitionListConverterAwareTrait;
use App\Domain\Shared\Validator\ValidatorAwareTrait;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class Command extends BaseCommand
{
    use ChoicesBuilderAwareTrait, DefinitionListConverterAwareTrait, ValidatorAwareTrait;

    const int SUCCESS = BaseCommand::SUCCESS;
    const int FAILURE = BaseCommand::FAILURE;
    const int INVALID = BaseCommand::INVALID;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $question
     * @return mixed
     */
    protected function askQuestion(InputInterface $input, OutputInterface $output, string $question): mixed
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        return $helper->ask($input, $output, new Question($question));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @return void
     */
    protected function interactQuestion(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $input->setArgument($argument, $this->askQuestion($input, $output, $question));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $question
     * @param array $choices
     * @param bool $useKeyAsChoiceValue
     * @return mixed
     */
    protected function askChoiceQuestion(
        InputInterface  $input,
        OutputInterface $output,
        string          $question,
        array           $choices,
        bool            $useKeyAsChoiceValue = false
    ): mixed
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $choice = $helper->ask($input, $output, new ChoiceQuestion($question, $choices));
        return $useKeyAsChoiceValue ? array_search($choice, $choices, true) : $choice;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @param array $choices
     * @param bool $useKeyAsChoiceValue
     * @return void
     */
    protected function interactChoiceQuestion(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question,
        array           $choices,
        bool            $useKeyAsChoiceValue = false
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $input->setArgument(
                $argument,
                $this->askChoiceQuestion($input, $output, $question, $choices, $useKeyAsChoiceValue)
            );
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $question
     * @param ChoicesResolver $choicesResolver
     * @return mixed
     */
    protected function askChoiceQuestionWithChoicesResolver(
        InputInterface  $input,
        OutputInterface $output,
        string          $question,
        ChoicesResolver $choicesResolver
    ): mixed {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $choice = $helper->ask($input, $output, new ChoiceQuestion($question, $choicesResolver->getChoices()));
        return $choicesResolver->resolveChoice($choice);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @param ChoicesResolver $choicesResolver
     * @return void
     */
    protected function interactChoiceQuestionWithChoicesResolver(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question,
        ChoicesResolver $choicesResolver
    ): void {
        if ($input->getArgument($argument) === null) {
            $input->setArgument(
                $argument,
                $this->askChoiceQuestionWithChoicesResolver($input, $output, $question, $choicesResolver)
            );
        }
    }

    /**
     * @deprecated
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argument
     * @param string $question
     * @param ChoosableInterface[] $choosables
     * @param bool $useKeyAsChoiceValue
     * @return void
     */
    protected function interactChoiceQuestionWithChoosables(
        InputInterface  $input,
        OutputInterface $output,
        string          $argument,
        string          $question,
        array           $choosables,
        bool            $useKeyAsChoiceValue = false
    ): void {
        $this->interactChoiceQuestion(
            $input,
            $output,
            $argument,
            $question,
            $this->choicesBuilder->build($choosables),
            $useKeyAsChoiceValue
        );
    }
}

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

use App\Domain\Shared\Console\Question\ChoicesResolver;
use App\Domain\Shared\Console\Style\DefinitionListConverterAwareTrait;
use App\Domain\Shared\Validator\ValidatorAwareTrait;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Throwable;
use ValueError;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class Command extends BaseCommand implements LoggerAwareInterface
{
    use DefinitionListConverterAwareTrait, LoggerAwareTrait, ValidatorAwareTrait;

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
    ): mixed
    {
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
    ): void
    {
        if ($input->getArgument($argument) === null) {
            $input->setArgument(
                $argument,
                $this->askChoiceQuestionWithChoicesResolver($input, $output, $question, $choicesResolver)
            );
        }
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @param string $sentinel
     * @return bool|string|null
     */
    protected function parseBoolOption(InputInterface $input, string $option, string $sentinel = '~'): bool|string|null
    {
        $value = $input->getOption($option);
        if ($value === null || $value === $sentinel) {
            return $value;
        }
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($value === null) {
            throw new ValueError(sprintf('The %s option must be a boolean value.', $option));
        }
        return $value;
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return float|null
     */
    protected function parseFloatArgument(InputInterface $input, string $argument): ?float
    {
        $value = $input->getArgument($argument);
        if ($value === null) {
            return null;
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        return floatval($value);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return float|false|null
     */
    protected function parseFloatOption(InputInterface $input, string $option): float|false|null
    {
        $value = $input->getOption($option);
        if ($value === null || $value === false) {
            return $value;
        }
        if (!is_numeric($value)) {
            throw new ValueError(sprintf('The %s option must be a numeric value.', $option));
        }
        return floatval($value);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @return int|null
     */
    protected function parseIntArgument(InputInterface $input, string $argument): ?int
    {
        $value = $input->getArgument($argument);
        if ($value === null) {
            return null;
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('The %s argument must be a numeric value.', $argument));
        }
        return intval($value);
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @return int|false|null
     */
    protected function parseIntOption(InputInterface $input, string $option): int|false|null
    {
        $value = $input->getOption($option);
        if ($value === null || $value === false) {
            return $value;
        }
        if (!is_numeric($value)) {
            throw new ValueError(sprintf('The %s option must be a numeric value.', $option));
        }
        return intval($value);
    }

    /**
     * @param InputInterface $input
     * @param string $argument
     * @param bool $trim
     * @return string|null
     */
    protected function parseStringArgument(InputInterface $input, string $argument, bool $trim = false): ?string
    {
        $value = $input->getArgument($argument);
        if ($value === null) {
            return null;
        }
        $value = strval($value);
        return $trim ? trim($value) : $value;
    }

    /**
     * @param InputInterface $input
     * @param string $option
     * @param bool $trim
     * @return int|false|null
     */
    protected function parseStringOption(InputInterface $input, string $option, bool $trim = false): string|false|null
    {
        $value = $input->getOption($option);
        if ($value === null || $value === false) {
            return $value;
        }
        $value = strval($value);
        return $trim ? trim($value) : $value;
    }

    /**
     * @param mixed $value
     * @param array|Constraint|null $constrains
     * @param array|string|GroupSequence|null $groups
     * @return void
     */
    protected function validate(
        mixed                           $value,
        array|null|Constraint           $constrains = null,
        array|null|string|GroupSequence $groups = null
    ): void
    {
        $errors = $this->validator->validate($value, $constrains, $groups);
        if (count($errors) > 0) {
            throw new RuntimeException((string)$errors);
        }
    }

    /**
     * @param Throwable $throwable
     * @return void
     */
    protected function logThrowable(Throwable $throwable): void
    {
        $f = $throwable instanceof FlattenException ? $throwable : FlattenException::createFromThrowable($throwable);
        foreach ($f->toArray() as $context) {
            $this->logger->error('{message}', $context);
        }
    }
}

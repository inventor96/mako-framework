<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments;

use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\cli\input\arguments\exceptions\InvalidArgumentException;
use mako\cli\input\arguments\exceptions\MissingArgumentException;
use mako\cli\input\arguments\exceptions\UnexpectedValueException;
use mako\common\traits\SuggestionTrait;

use function array_keys;
use function array_shift;
use function current;
use function explode;
use function preg_match;
use function strlen;
use function strpos;
use function substr;
use function vsprintf;

/**
 * Argument parser.
 */
class ArgvParser
{
	use SuggestionTrait;

	/**
	 * Regex that matches integers.
	 */
	protected const string INT_REGEX = '/^([+-]?[1-9]\d*|0)$/';

	/**
	 * Regex that matches floats.
	 */
	protected const string FLOAT_REGEX = '/^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$/';

	/**
	 * Arguments.
	 */
	protected array $arguments = [];

	/**
	 * Map.
	 */
	protected array $map = [];

	/**
	 * Positional arguments.
	 */
	protected array $positionals = [];

	/**
	 * Parsed arguments.
	 */
	protected array $parsed = [];

	/**
	 * Should unknown arguments be ignored?
	 */
	protected bool $ignoreUnknownArguments = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $argv,
		array $arguments = []
	) {
	   $this->addArguments($arguments);
	}

	/**
	 * Returns the registered arguments.
	 */
	public function getArguments(): array
	{
		return $this->arguments;
	}

	/**
	 * Tries to find a suggestion for the invalid argument name.
	 */
	protected function findArgumentSuggestion(string $name): ?string
	{
		return $this->suggest($name, array_keys($this->map));
	}

	/**
	 * Returns an argument based on its name.
	 */
	protected function getArgument(string $name): ?Argument
	{
		if (!isset($this->map[$name])) {
			if ($this->ignoreUnknownArguments === false) {
				throw new InvalidArgumentException(vsprintf('Unknown argument [ %s ].', [$name]), $this->findArgumentSuggestion($name));
			}

			return null;
		}

		return $this->arguments[$this->map[$name]];
	}

	/**
	 * Clears the parsed argument cache.
	 */
	public function clearCache(): ArgvParser
	{
		$this->parsed = [];

		return $this;
	}

	/**
	 * Add argument.
	 */
	public function addArgument(Argument $argument): void
	{
		$name = $argument->getName();

		$normalizedName = $argument->getNormalizedName();

		// Ensure that the argument name is unique

		if (isset($this->arguments[$normalizedName])) {
			throw new ArgumentException(vsprintf('Ambiguous argument name. [ %s ] will collide with [ %s ].', [$name, $this->arguments[$normalizedName]->getName()]));
		}

		// Check if the argument has an alias and that it's unique

		if (!empty($alias = $argument->getAlias())) {
			if (isset($this->map[$alias])) {
				throw new ArgumentException(vsprintf('Duplicate alias detected [ %s ]. The alias of [ %s ] will collide with the alias of [ %s ].', [$alias, $name, $this->getArgument($alias)->getName()]));
			}

			$this->map[$alias] = $normalizedName;
		}

		// Add to array of positionals if it's a positional argument

		if ($argument->isPositional()) {
			$this->positionals[] = $name;
		}

		// Add mapping and argument

		$this->map[$name] = $normalizedName;

		$this->arguments[$normalizedName] = $argument;

		// Reset the parsed array since we have added new arguments

		$this->clearCache();
	}

	/**
	 * Add arguments.
	 */
	public function addArguments(array $arguments): void
	{
		foreach ($arguments as $argument) {
			$this->addArgument($argument);
		}
	}

	/**
	 * Casts the value to the desired type.
	 */
	protected function castValue(Argument $argument, ?string $token, bool|string $value): bool|float|int|string
	{
		if ($argument->isInt()) {
			if (preg_match(static::INT_REGEX, $value) !== 1) {
				throw new UnexpectedValueException(vsprintf('The [ %s ] argument expects an integer.', [$token ?? $argument->getName()]));
			}

			$value = (int) $value;
		}
		elseif ($argument->isFloat()) {
			if (preg_match(static::FLOAT_REGEX, $value) !== 1) {
				throw new UnexpectedValueException(vsprintf('The [ %s ] argument expects a float.', [$token ?? $argument->getName()]));
			}

			$value = (float) $value;
		}

		return $value;
	}

	/**
	 * Store the value.
	 */
	protected function storeValue(Argument $argument, ?string $token, null|bool|string $value): void
	{
		if ($value === null) {
			throw new ArgumentException(vsprintf('Missing value for argument [ %s ].', [$token]));
		}

	   $value = $this->castValue($argument, $token, $value);

		if ($argument->isArray()) {
			$this->parsed[$argument->getNormalizedName()][] = $value;
		}
		else {
			$this->parsed[$argument->getNormalizedName()] = $value;
		}
	}

	/**
	 * Stores option values.
	 */
	protected function storeOptionValue(Argument $argument, string $token, ?string $value, array &$tokens): void
	{
		if ($argument->isBool()) {
			if ($value !== null) {
				throw new ArgumentException(vsprintf('The [ %s ] argument is a boolean and does not accept values.', [$token]));
			}

			$value = true;
		}
		else {
			if (($next = current($tokens)) !== false && strpos($next, '-') !== 0) {
				$value = array_shift($tokens);
			}
		}

		$this->storeValue($argument, $token, $value);
	}

	/**
	 * Parses an option.
	 */
	protected function parseOption(string $token, array &$tokens): void
	{
		$value = null;

		if (strpos($token, '=') !== false) {
			[$token, $value] = explode('=', $token, 2);
		}

		if (($argument = $this->getArgument($token)) === null) {
			return;
		}

		$this->storeOptionValue($argument, $token, $value, $tokens);
	}

	/**
	 * Parses an alias.
	 */
	protected function parseAlias(string $token, array &$tokens): void
	{
		$value = null;

		if (strlen($token) > 2) {
			[$token, $value] = [substr($token, 0, 2), substr($token, 2)];
		}

		if (($argument = $this->getArgument($token)) === null) {
			return;
		}

		if ($value !== null && $argument->isBool()) {
			$chained = $value;

			$value = null;
		}

		$this->storeOptionValue($argument, $token, $value, $tokens);

		if (isset($chained)) {
			$this->parseAlias("-{$chained}", $tokens);
		}
	}

	/**
	 * Parses a positional argument.
	 */
	protected function parsePositional(string $token, array &$positionals, array &$tokens, bool $parseOptions): void
	{
		if (empty($positionals)) {
			if ($this->ignoreUnknownArguments === false) {
				throw new InvalidArgumentException(vsprintf('Unknown positional argument with value [ %s ].', [$token]));
			}

			return;
		}

		$argument = $this->arguments[array_shift($positionals)];

		while (true) {
			$this->storeValue($argument, null, $token);

			if (!$argument->isArray() || ($next = current($tokens)) === false || ($parseOptions && strpos($next, '-') === 0)) {
				break;
			}

			$token = array_shift($tokens);
		}
	}

	/**
	 * Parses the arguments.
	 */
	public function parse(bool $ignoreUnknownArguments = false): array
	{
		if (empty($this->parsed)) {
			$this->ignoreUnknownArguments = $ignoreUnknownArguments;

			// Parse input

			$tokens = $this->argv;

			$parseOptions = true;

			$positionals = $this->positionals;

			while (($token = array_shift($tokens)) !== null) {
				if ($token === '--') {
					$parseOptions = false;
				}
				elseif ($parseOptions && strpos($token, '--') === 0) {
					$this->parseOption($token, $tokens);
				}
				elseif ($parseOptions && strpos($token, '-') === 0) {
					$this->parseAlias($token, $tokens);
				}
				else {
					$this->parsePositional($token, $positionals, $tokens, $parseOptions);
				}
			}

			// Ensure that all required arguments are set
			// and fill in default values for missing optional arguments

			foreach ($this->arguments as $normalizedName => $argument) {
				if (!isset($this->parsed[$normalizedName])) {
					if (!$argument->isOptional()) {
						throw new MissingArgumentException(vsprintf('Missing required argument [ %s ].', [$argument->getName()]));
					}

					$this->parsed[$normalizedName] = $argument->getDefaultValue();
				}
			}
		}

		return $this->parsed;
	}

	/**
	 * Returns the value of a parsed argument.
	 */
	public function getArgumentValue(string $argument, mixed $default = null, bool $ignoreUnknownArguments = false): mixed
	{
		$parsed = $this->parse($ignoreUnknownArguments);

		if (isset($this->map[$argument])) {
			return $parsed[$this->map[$argument]] ?? $default;
		}

		return $default;
	}
}

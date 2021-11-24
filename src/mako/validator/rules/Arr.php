<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function is_array;
use function sprintf;

/**
 * Arr rule.
 */
class Arr extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, string $field, array $input): bool
	{
		return is_array($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain an array.', $field);
	}
}

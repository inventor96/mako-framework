<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Boolean true rule.
 */
class BooleanTrue extends Rule implements RuleInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return $value === true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain the boolean value TRUE.', $field);
	}
}
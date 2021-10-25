<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Greater than rule.
 *
 * @author Frederic G. Østby
 */
class GreaterThan extends Rule implements RuleInterface
{
	/**
	 * Greater than.
	 *
	 * @var mixed
	 */
	protected $greaterThan;

	/**
	 * Constructor.
	 *
	 * @param mixed $greaterThan Greater than
	 */
	public function __construct($greaterThan)
	{
		$this->greaterThan = $greaterThan;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['greaterThan'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value > $this->greaterThan;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be greater than %2$s.', $field, $this->greaterThan);
	}
}

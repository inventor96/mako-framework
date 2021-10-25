<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function checkdnsrr;
use function explode;
use function sprintf;
use function strpos;

/**
 * Email domain rule.
 *
 * @author Frederic G. Østby
 */
class EmailDomain extends Rule implements RuleInterface
{
	/**
	 * Returns TRUE if the domain has a MX record and FALSE if not.
	 *
	 * @param  string $domain Domain
	 * @return bool
	 */
	protected function hasMXRecord(string $domain): bool
	{
		return checkdnsrr($domain, 'MX');
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		if(empty($value) || strpos($value, '@') === false)
		{
			return false;
		}

		[, $domain] = explode('@', $value);

		return $this->hasMXRecord($domain);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid e-mail address.', $field);
	}
}

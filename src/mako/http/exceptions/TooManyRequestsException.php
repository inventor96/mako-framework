<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

use Throwable;

/**
 * Too many requests exception.
 */
class TooManyRequestsException extends HttpStatusException
{
	/**
	 * {@inheritDoc}
	 */
	protected $defaultMessage = 'You have made too many requests to the server.';

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param \Throwable|null $previous Previous exception
	 */
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct(429, $message, $previous);
	}
}

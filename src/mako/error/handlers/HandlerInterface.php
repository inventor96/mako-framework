<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use Throwable;

/**
 * Handler interface.
 */
interface HandlerInterface
{
	/**
	 * Handles the exception.
	 *
	 * @param  \Throwable $exception Exception to handle
	 * @return mixed
	 */
	public function handle(Throwable $exception);
}

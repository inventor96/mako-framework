<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTime;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTime class.
 *
 * @author Frederic G. Østby
 */
class Time extends DateTime implements TimeInterface
{
	use TimeTrait;
}

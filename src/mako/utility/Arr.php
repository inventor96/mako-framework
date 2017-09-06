<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

/**
 * Array helper.
 *
 * @author Frederic G. Østby
 */
class Arr
{
	/**
	 * Sets an array value using "dot notation".
	 *
	 * @param array  &$array Array you want to modify
	 * @param string $path   Array path
	 * @param mixed  $value  Value to set
	 */
	public static function set(array &$array, string $path, $value)
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if(!isset($array[$segment]) || !is_array($array[$segment]))
			{
				$array[$segment] = [];
			}

			$array =& $array[$segment];
		}

		$array[array_shift($segments)] = $value;
	}

	/**
	 * Search for an array value using "dot notation". Returns TRUE if the array key exists and FALSE if not.
	 *
	 * @param  array  $array Array we're goint to search
	 * @param  string $path  Array path
	 * @return bool
	 */
	public static function has(array $array, string $path): bool
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !isset($array[$segment]))
			{
				return false;
			}

			$array = $array[$segment];
		}

		return true;
	}

	/**
	 * Returns value from array using "dot notation".
	 *
	 * @param  array      $array   Array we're going to search
	 * @param  string     $path    Array path
	 * @param  mixed|null $default Default return value
	 * @return mixed|null
	 */
	public static function get(array $array, string $path, $default = null)
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !isset($array[$segment]))
			{
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Deletes an array value using "dot notation".
	 *
	 * @param  array  &$array Array you want to modify
	 * @param  string $path   Array path
	 * @return bool
	 */
	public static function delete(array &$array, string $path): bool
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if(!isset($array[$segment]) || !is_array($array[$segment]))
			{
				return false;
			}

			$array =& $array[$segment];
		}

		unset($array[array_shift($segments)]);

		return true;
	}

	/**
	 * Returns a random value from an array.
	 *
	 * @param  array $array Array you want to pick a random value from
	 * @return mixed
	 */
	public static function random(array $array)
	{
		return $array[array_rand($array)];
	}

	/**
	 * Returns TRUE if the array is associative and FALSE if not.
	 *
	 * @param  array $array Array to check
	 * @return bool
	 */
	public static function isAssoc(array $array): bool
	{
		return count(array_filter(array_keys($array), 'is_string')) === count($array);
	}

	/**
	 * Returns the values from a single column of the input array, identified by the key.
	 *
	 * @param  array  $array Array to pluck from
	 * @param  string $key   Array key
	 * @return array
	 */
	public static function pluck(array $array, string $key): array
	{
		return array_map(function($value) use ($key)
		{
			return is_object($value) ? $value->$key : $value[$key];
		}, $array);
	}
}

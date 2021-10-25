<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use RuntimeException;

use function function_exists;
use function zend_shm_cache_clear;
use function zend_shm_cache_delete;
use function zend_shm_cache_fetch;
use function zend_shm_cache_store;

/**
 * Zend memory store.
 *
 * @deprecated 7.0
 * @author Frederic G. Østby
 */
class ZendMemory extends Store
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if(function_exists('zend_shm_cache_store') === false)
		{
			throw new RuntimeException('Zend memory cache is not available on your system.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function put(string $key, $data, int $ttl = 0): bool
	{
		return zend_shm_cache_store($this->getPrefixedKey($key), $data, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return (zend_shm_cache_fetch($this->getPrefixedKey($key)) !== false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key)
	{
		return zend_shm_cache_fetch($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return zend_shm_cache_delete($this->getPrefixedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		return zend_shm_cache_clear();
	}
}

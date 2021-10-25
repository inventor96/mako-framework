<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\crypto;

use mako\security\crypto\encrypters\EncrypterInterface;
use mako\security\Signer;

/**
 * Crypto wrapper.
 *
 * @author Frederic G. Østby
 */
class Crypto
{
	/**
	 * Crypto adapter.
	 *
	 * @var \mako\security\crypto\encrypters\EncrypterInterface
	 */
	protected $adapter;

	/**
	 * Signer.
	 *
	 * @var \mako\security\Signer
	 */
	protected $signer;

	/**
	 * Constructor.
	 *
	 * @param \mako\security\crypto\encrypters\EncrypterInterface $adapter Crypto adapter
	 * @param \mako\security\Signer                               $signer  signer instance
	 */
	public function __construct(EncrypterInterface $adapter, Signer $signer)
	{
		$this->adapter = $adapter;

		$this->signer = $signer;
	}

	/**
	 * Encrypts string.
	 *
	 * @param  string $string String to encrypt
	 * @return string
	 */
	public function encrypt(string $string): string
	{
		return $this->signer->sign($this->adapter->encrypt($string));
	}

	/**
	 * Decrypts string.
	 *
	 * @param  string      $string String to decrypt
	 * @return bool|string
	 */
	public function decrypt(string $string)
	{
		$string = $this->signer->validate($string);

		if($string === false)
		{
			throw new CryptoException('Ciphertex has been modified or an invalid authentication key has been provided.');
		}

		return $this->adapter->decrypt($string);
	}
}

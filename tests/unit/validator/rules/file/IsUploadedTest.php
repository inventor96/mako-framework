<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\http\request\UploadedFile;
use mako\tests\TestCase;
use mako\validator\rules\file\IsUploaded;
use Mockery;

/**
 * @group unit
 */
class IsUploadedTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if(PHP_VERSION_ID >= 80100)
		{
			$this->markTestSkipped('Mockery must be updated to support PHP 8.1.');
		}
	}

	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new IsUploaded;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new IsUploaded;

		$uploadedFile = Mockery::mock(UploadedFile::class);

		$uploadedFile->shouldReceive('isUploaded')->once()->andReturnTrue();

		$uploadedFile->shouldReceive('hasError')->once()->andReturnFalse();

		$this->assertTrue($rule->validate($uploadedFile, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new IsUploaded;

		$this->assertFalse($rule->validate(false, []));

		//

		$rule = new IsUploaded;

		$uploadedFile = Mockery::mock(UploadedFile::class);

		$uploadedFile->shouldReceive('isUploaded')->once()->andReturnFalse();

		$this->assertFalse($rule->validate($uploadedFile, []));

		//

		$rule = new IsUploaded;

		$uploadedFile = Mockery::mock(UploadedFile::class);

		$uploadedFile->shouldReceive('isUploaded')->once()->andReturnTrue();

		$uploadedFile->shouldReceive('hasError')->once()->andReturnTrue();

		$this->assertFalse($rule->validate($uploadedFile, []));

		$this->assertSame('The foobar must be an uploaded file.', $rule->getErrorMessage('foobar'));
	}
}

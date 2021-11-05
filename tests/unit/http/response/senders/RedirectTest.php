<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\builders;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\response\senders\Redirect;
use mako\tests\TestCase;
use Mockery;
use RuntimeException;

/**
 * @group unit
 */
class RedirectTest extends TestCase
{
	/**
	 *
	 */
	public function testSend(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(302);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$this->assertSame(302, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithConstructorStatus(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(302);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org', 302);

		$this->assertSame(302, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(302);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->setStatus(302);

		$this->assertSame(302, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus301(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(301);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->movedPermanently();

		$this->assertSame(301, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus302(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(302);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->found();

		$this->assertSame(302, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus303(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(303);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->seeOther();

		$this->assertSame(303, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus307(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(307);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->temporaryRedirect();

		$this->assertSame(307, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testSendWithStatus308(): void
	{
		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('Location', 'http://example.org');

		$response = Mockery::mock(Response::class);

		$response->shouldReceive('setStatus')->once()->with(308);

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$response->shouldReceive('sendHeaders')->once();

		//

		$redirect = new Redirect('http://example.org');

		$redirect->permanentRedirect();

		$this->assertSame(308, $redirect->getStatus());

		$redirect->send($request, $response);
	}

	/**
	 *
	 */
	public function testInvalidStatusCode(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Unsupported redirect status code [ 306 ].');

		new Redirect('http://example.org', 306);
	}
}

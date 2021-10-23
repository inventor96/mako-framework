<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Headers;
use mako\http\routing\Controller;
use mako\http\routing\Dispatcher;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\http\routing\Route;
use mako\syringe\Container;
use mako\tests\TestCase;
use Mockery;
use RuntimeException;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class InjectMe
{
	public function helloWorld()
	{
		return 'Hello, world!';
	}
}

class SimpleController extends Controller
{
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function foo()
	{
		$this->response->getHeaders()->add('X-Foo-Bar', 'Foo Bar');

		return 'Hello, world!';
	}

	public function bar($who)
	{
		return 'Hello, ' . $who . '!';
	}

	public function baz(InjectMe $injectMe)
	{
		return $injectMe;
	}
}

class InvokeController extends Controller
{
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function __invoke()
	{
		$this->response->getHeaders()->add('X-Foo-Bar', 'Foo Bar');

		return 'Hello, world!';
	}
}

class ControllerWithBeforeFilter extends Controller
{
	public function beforeAction()
	{
		return 'Before action';
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class ControllerWithNullBeforeFilter extends Controller
{
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function beforeAction(): void
	{
		$this->response->getHeaders()->add('X-Foo-Bar', 'Foo Bar');
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class ControllerWithAfterFilter extends Controller
{
	protected $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
	}

	public function afterAction(): void
	{
		$this->response->setBody(strtoupper($this->response->getBody()));
	}

	public function foo()
	{
		return 'Hello, world!';
	}
}

class ControllerWithInjection extends Controller
{
	protected $injectMe;

	public function __construct(InjectMe $injectMe)
	{
		$this->injectMe = $injectMe;
	}

	public function foo()
	{
		return $this->injectMe->helloWorld();
	}
}

class FooMiddleware implements MiddlewareInterface
{
	protected $separator;

	public function __construct($separator = '_')
	{
		$this->separator = $separator;
	}

	public function execute(Request $request, Response $response, Closure $next): Response
	{
		return $response->setBody(str_replace(' ', $this->separator, $next($request, $response)->getBody()));
	}
}

class BazMiddleware implements MiddlewareInterface
{
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$response = $next($request, $response);

		$response->setBody('AA ' . $response->getBody() . ' AA');

		return $response;
	}
}

class BaxMiddleware implements MiddlewareInterface
{
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$response = $next($request, $response);

		$response->setBody('BB ' . $response->getBody() . ' BB');

		return $response;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class DispatcherTest extends TestCase
{
	/**
	 *
	 */
	public function testClosureAction(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'Hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureActionWithParams(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn(function(Response $response, $who)
		{
			$response->getHeaders()->add('X-Foo-Bar', 'Foo Bar');

			return 'Hello, ' . $who . '!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('X-Foo-Bar', 'Foo Bar');

		$response = Mockery::mock(Response::class)->makePartial();

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, Kitty!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerAction(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([SimpleController::class, 'foo']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('X-Foo-Bar', 'Foo Bar');

		$response = Mockery::mock(Response::class)->makePartial();

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testInvokeControllerAction(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn(InvokeController::class);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('X-Foo-Bar', 'Foo Bar');

		$response = Mockery::mock(Response::class)->makePartial();

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionWithParams(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([SimpleController::class, 'bar']);

		$route->shouldReceive('getParameters')->once()->andReturn(['who' => 'Kitty']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, Kitty!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerWithNullBeforeFilter(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([ControllerWithNullBeforeFilter::class, 'foo']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$responseHeaders = Mockery::mock(Headers::class);

		$responseHeaders->shouldReceive('add')->once()->with('X-Foo-Bar', 'Foo Bar');

		$response = Mockery::mock(Response::class)->makePartial();

		$response->shouldReceive('getHeaders')->once()->andReturn($responseHeaders);

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerWithBeforeFilter(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([ControllerWithBeforeFilter::class, 'foo']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Before action', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionWithAfterFilter(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([ControllerWithAfterFilter::class, 'foo']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('HELLO, WORLD!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddleware(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', FooMiddleware::class);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,_world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testGlobalMiddleware(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', BazMiddleware::class);

		$dispatcher->setMiddlewareAsGlobal(['test']);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('AA hello, world! AA', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewarePriority(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->times(3)->andReturn(['a', 'b']);

		$route->shouldReceive('getAction')->times(3)->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->times(3)->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('a', BazMiddleware::class);
		$dispatcher->registerMiddleware('b', BaxMiddleware::class);

		//

		$dispatcher->setMiddlewarePriority(['a' => 1, 'b' => 2]);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('AA BB hello, world! BB AA', $response->getBody());

		//

		$dispatcher->setMiddlewarePriority(['a' => 2, 'b' => 1]);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('BB AA hello, world! AA BB', $response->getBody());

		//

		$dispatcher->resetMiddlewarePriority();

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('AA BB hello, world! BB AA', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewareRegistrationWithPriority(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->times(1)->andReturn(['a', 'b']);

		$route->shouldReceive('getAction')->times(1)->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->times(1)->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('a', BazMiddleware::class, 2);
		$dispatcher->registerMiddleware('b', BaxMiddleware::class, 1);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('BB AA hello, world! AA BB', $response->getBody());
	}

	/**
	 *
	 */
	public function testUnregisteredMiddleware(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('No middleware named [ foobar ] has been registered.');

		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->once()->andReturn(['foobar']);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);
	}

	/**
	 *
	 */
	public function testMiddlewareWithUnnamedArguments(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test("~")']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', FooMiddleware::class);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,~world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testMiddlewareWithNamedArguments(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getMiddleware')->once()->andReturn(['test("separator":"~")']);

		$route->shouldReceive('getAction')->once()->andReturn(function()
		{
			return 'hello, world!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response, $container);

		$dispatcher->registerMiddleware('test', FooMiddleware::class);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('hello,~world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerInjection(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([ControllerWithInjection::class, 'foo']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureWithReversedParameterOrder(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn(function($world, $hello)
		{
			return $hello . ', ' . $world . '!';
		});

		$route->shouldReceive('getParameters')->once()->andReturn(['hello' => 'Hello', 'world' => 'world']);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$dispatcher = new Dispatcher($request, $response);

		$response = $dispatcher->dispatch($route);

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testClosureParameterInjection(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn(function(Request $request)
		{
			return $request;
		});

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Request::class)->andReturn($request);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertInstanceOf(Request::class, $response->getBody());
	}

	/**
	 *
	 */
	public function testControllerActionParameterInjection(): void
	{
		$route = Mockery::mock(Route::class);

		$route->shouldReceive('getAction')->once()->andReturn([SimpleController::class, 'baz']);

		$route->shouldReceive('getParameters')->once()->andReturn([]);

		$route->shouldReceive('getMiddleware')->once()->andReturn([]);

		$request = Mockery::mock(Request::class);

		$response = Mockery::mock(Response::class)->makePartial();

		$container = Mockery::mock(Container::class)->makePartial();

		$container->shouldReceive('get')->with(Response::class)->andReturn($response);

		$dispatcher = new Dispatcher($request, $response, $container);

		$response = $dispatcher->dispatch($route);

		$this->assertInstanceOf(InjectMe::class, $response->getBody());
	}
}

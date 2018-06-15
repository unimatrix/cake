<?php

namespace Unimatrix\Cake\Test\TestCase\Error\Middleware;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Exception\MissingControllerException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Psr\Log\LoggerInterface;
use Unimatrix\Cake\Error\Middleware\EmailErrorHandlerMiddleware;
use Unimatrix\Cake\Lib\Misc;
use Exception;

class EmailErrorHandlerMiddlewareTest extends TestCase
{
    protected $debug;
    protected $handler;

    public function setUp() {
        parent::setUp();
        $this->debug = Configure::read('debug');
        Configure::write('debug', false);
        $this->handler = $this->getMockBuilder(EmailErrorHandlerMiddleware::class)
            ->setMethods(['_email'])
            ->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        Log::reset();
        Log::setConfig('error_test', [
            'engine' => $logger
        ]);
    }

    public function tearDown() {
        parent::tearDown();
        unset($this->handler);
        Configure::write('debug', $this->debug);
        Log::drop('error_test');
    }

    public function testMiddlewareWithoutException() {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();
        $middleware = $this->handler;
        $next = function ($req, $res) {
            return $res;
        };

        $this->handler->expects($this->never())
            ->method('_email');
        $result = $middleware($request, $response, $next);
        $this->assertSame($result, $response);
    }

    public function testMiddlewareWithException() {
        $content = 'Test exception.';
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();
        $middleware = $this->handler;
        $next = function ($req, $res) use ($content) {
            throw new Exception($content);
        };

        $this->handler->expects($this->once())
            ->method('_email')
            ->with('Website Exception', $this->stringContains('Test exception'));
        $result = $middleware($request, $response, $next);
        $this->assertNotSame($result, $response);
        $this->assertEquals(500, $result->getStatusCode());
    }

    public function testHandleException() {
        $content = 'Test exception.';
        $exception = new Exception($content);
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();
        $this->handler->expects($this->once())
            ->method('_email')
            ->with('Website Exception', Misc::dump($exception, $content, true));

        $this->handler->handleException($exception, $request, $response);
    }

    public function testHandleExceptionSkip() {
        $message = 'Test exception skip.';
        $exception1 = new NotFoundException($message);
        $exception2 = new MissingRouteException($message);
        $exception3 = new MissingControllerException($message);
        $exception4 = new RecordNotFoundException($message);
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();
        $this->handler->expects($this->never())
            ->method('_email');

        $this->handler->handleException($exception1, $request, $response);
        $this->handler->handleException($exception2, $request, $response);
        $this->handler->handleException($exception3, $request, $response);
        $this->handler->handleException($exception4, $request, $response);
    }
}

<?php

namespace Unimatrix\Cake\Test\TestCase\Error;

use Cake\TestSuite\TestCase;
use Cake\Log\Log;
use Cake\Core\Configure;
use Cake\Error\FatalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Exception\MissingControllerException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Psr\Log\LoggerInterface;
use Unimatrix\Cake\Error\EmailErrorHandler;
use Unimatrix\Cake\Lib\Misc;
use Exception;

class TestEmailErrorHandler extends EmailErrorHandler
{
    protected function _displayException($exception) {
        // noop
    }
}

class EmailErrorHandlerTest extends TestCase
{
    protected $debug;
    protected $handler;

    private static $errorLevel;

    public function setUp() {
        parent::setUp();
        $this->debug = Configure::read('debug');
        Configure::write('debug', false);
        $this->handler = $this->getMockBuilder(TestEmailErrorHandler::class)
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
        Log::reset();
        restore_error_handler();
        restore_exception_handler();
        error_reporting(self::$errorLevel);
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$errorLevel = error_reporting();
    }

    public function testHandleErrorAsNotice() {
        $this->handler->register();
        $this->handler->expects($this->never())
            ->method('_email');

        $wrong = $wrong + 1;
    }

    public function testHandleErrorAsWarning() {
        $this->handler->register();
        $this->handler->expects($this->never())
            ->method('_email');

        include 'invalid.file';
    }

    public function testHandleErrorAsDeprecated() {
        $this->handler->register();
        $title = 'Deprecated Error';
        $message = 'Test deprecated';
        $this->handler->expects($this->once())
            ->method('_email')
            ->with('Website Error', Misc::dump([
                'type' => $title,
                'description' => $message,
                'file' => __FILE__,
                'line' => 95,
                'code' => 16384,
                'context' => [
                    'title' => $title,
                    'message' => $message
                ]
            ], $title . ': ' . $message, true));

        trigger_error($message, E_USER_DEPRECATED);
    }

    public function testHandleErrorAsErrorException() {
        $this->handler->register();
        $title = 'Error Error';
        $message = 'Test fatal error';
        $this->handler->expects($this->exactly(2))
            ->method('_email')
            ->withConsecutive([
                'Website Error', Misc::dump([
                    'type' => $title,
                    'description' => $message,
                    'file' => __FILE__,
                    'line' => 118,
                    'code' => 256,
                    'context' => [
                        'title' => $title,
                        'message' => $message
                    ]
                ], $title . ': ' . $message, true)
            ],['Website Exception', $this->stringContains(FatalErrorException::class)]);

        trigger_error($message, E_USER_ERROR);
    }

    public function testHandleException() {
        $content = 'Test exception.';
        $exception = new Exception($content);
        $this->handler->expects($this->once())
            ->method('_email')
            ->with('Website Exception', Misc::dump($exception, $content, true));

        $this->handler->handleException($exception);
    }

    public function testHandleExceptionSkip() {
        $message = 'Test exception skip.';
        $exception1 = new NotFoundException($message);
        $exception2 = new MissingRouteException($message);
        $exception3 = new MissingControllerException($message);
        $exception4 = new RecordNotFoundException($message);
        $this->handler->expects($this->never())
            ->method('_email');

        $this->handler->handleException($exception1);
        $this->handler->handleException($exception2);
        $this->handler->handleException($exception3);
        $this->handler->handleException($exception4);
    }
}

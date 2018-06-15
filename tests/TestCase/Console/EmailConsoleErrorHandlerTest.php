<?php

namespace Unimatrix\Cake\Test\TestCase\Console;

use Cake\TestSuite\TestCase;
use Cake\Log\Log;
use Cake\Core\Configure;
use Cake\Error\FatalErrorException;
use Psr\Log\LoggerInterface;
use Unimatrix\Cake\Console\EmailConsoleErrorHandler;
use Unimatrix\Cake\Lib\Misc;
use Exception;

class EmailConsoleErrorHandlerTest extends TestCase
{
    protected $debug;
    protected $handler;

    public function setUp() {
        parent::setUp();
        $this->debug = Configure::read('debug');
        Configure::write('debug', false);
        $stderr = $this->getMockBuilder('Cake\Console\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = $this->getMockBuilder(EmailConsoleErrorHandler::class)
            ->setMethods(['_stop', '_email'])
            ->setConstructorArgs([['stderr' => $stderr]])
            ->getMock();
        Log::drop('stderr');
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
    }

    public function testHandleErrorAsNotice() {
        $this->handler->expects($this->never())
            ->method('_email');

        $this->handler->handleError(E_NOTICE, 'This is a notice', '/some/file', 1337);
    }

    public function testHandleErrorAsWarning() {
        $this->handler->expects($this->never())
            ->method('_email');

        $this->handler->handleError(E_WARNING, 'This is a warning', '/some/file', 1337);
    }

    public function testHandleErrorAsStrict() {
        $file = '/some/file';
        $line = 1337;
        $title = 'Strict Error';
        $message = 'Test strict error';
        $this->handler->expects($this->once())
            ->method('_email')
            ->with('CLI Error', Misc::dump([
                'type' => $title,
                'description' => $message,
                'file' => $file,
                'line' => $line,
                'code' => 2048,
                'context' => NULL
            ], $title . ': ' . $message, true));

        $this->handler->handleError(E_STRICT , $message, $file, $line);
    }

    public function testHandleErrorAsErrorException() {
        $file = '/some/file';
        $line = 1337;
        $title = 'Error Error';
        $message = 'Test fatal error';
        $this->handler->expects($this->exactly(2))
            ->method('_email')
            ->withConsecutive([
                'CLI Error', Misc::dump([
                    'type' => $title,
                    'description' => $message,
                    'file' => $file,
                    'line' => $line,
                    'code' => 256,
                    'context' => NULL
                ], $title . ': ' . $message, true)
            ], ['CLI Exception', $this->stringContains(FatalErrorException::class)]);

        $this->handler->handleError(E_USER_ERROR, $message, $file, $line, null);
    }

    public function testHandleException() {
        $content = 'Test exception.';
        $exception = new Exception($content);

        phpinfo(8);
        exit;

        var_dump($exception);
        exit;

        $this->handler->expects($this->once())
            ->method('_email')
            ->with('CLI Exception', Misc::dump($exception, $content, true));

        $this->handler->handleException($exception);
    }
}

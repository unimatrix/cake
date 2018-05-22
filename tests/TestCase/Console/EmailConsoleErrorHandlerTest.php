<?php

namespace Unimatrix\Cake\Test\TestCase\Console;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Console\ConsoleErrorHandler;
use Cake\Error\PHP7ErrorException;
use Cake\Core\Configure;
use Exception;

class EmailConsoleErrorHandlerTest extends TestCase
{
    public function testSomething() {
        $this->assertTrue(true);
    }
}

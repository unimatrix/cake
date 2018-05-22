<?php

namespace Unimatrix\Cake\Test\TestCase\Error\Middleware;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Core\Configure;

class EmailErrorHandlerMiddlewareTest extends TestCase
{
    public function testSomething() {
        $this->assertTrue(true);
    }
}

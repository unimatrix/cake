<?php

namespace Unimatrix\Cake\Test\TestCase\Error;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Error\PHP7ErrorException;
use Cake\Error\ErrorHandler;
use Cake\Core\Configure;
use Exception;

class EmailErrorHandlerTest extends TestCase
{
    public function testSomething() {
        $this->assertTrue(true);
    }
}

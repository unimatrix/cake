<?php

namespace Unimatrix\Cake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Lib\Misc;

class MiscTest extends TestCase
{
    public function testOutput() {
        ob_start();
        Misc::dump(['home' => 'some big array']);
        $this->assertContains('some big array', ob_get_clean());
    }

    public function testReturn() {
        $result = Misc::dump(['home' => 'some big array'], false, true);
        $this->assertContains('some big array', $result);
    }

    public function testWithTitle() {
        $result = Misc::dump(['home' => 'some big array'], 'The Title', true);
        $this->assertContains('The Title', $result);
    }
}

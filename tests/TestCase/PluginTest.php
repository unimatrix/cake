<?php

namespace Unimatrix\Cake\Test\TestCase;

use Cake\TestSuite\TestCase;
use Cake\Http\MiddlewareQueue;
use Unimatrix\Cake\Plugin;

class PluginTest extends TestCase
{
    protected $server = null;
    protected $plugin;
    protected $stack;

    public function setUp() {
        parent::setUp();
        $this->server = $_SERVER;
        $this->stack = new MiddlewareQueue();
        $this->plugin = new Plugin();
    }

    public function tearDown() {
        parent::tearDown();
        $_SERVER = $this->server;
    }

    public function testPluginName() {
        $this->assertEquals('Unimatrix/Cake', $this->plugin->getName());
    }
}

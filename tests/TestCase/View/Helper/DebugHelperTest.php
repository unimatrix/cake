<?php

namespace Unimatrix\Cake\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Cake\I18n\Time;
use Unimatrix\Cake\View\Helper\DebugHelper;
use Exception;

class DebugHelperTest extends TestCase
{
    protected $helper;

	public function setUp() {
		parent::setUp();
		$view = new View(null);
		$this->helper = new DebugHelper($view);
	}

    public function testRequestTime() {
        $res = $this->helper->requestTime();
        $this->assertInternalType('float', $res);
    }

    public function testRequestStartTime() {
        $res = $this->helper->requestStartTime();
        try {
            Time::createFromFormat('U.u', $res);
        } catch(Exception $e) {
            $this->assertTrue(false, "Failed asserting that {$res} is a valid unix timestamp with microseconds #1.");
        }

        $GLOBALS['TIME_START'] = microtime(true);
        $res = $this->helper->requestStartTime(true);
        try {
            Time::createFromFormat('U.u', $res);
        } catch(Exception $e) {
            $this->assertTrue(false, "Failed asserting that {$res} is a valid unix timestamp with microseconds #2.");
        }
        unset($GLOBALS['TIME_START']);

        $res = $this->helper->requestStartTime(true);
        try {
            Time::createFromTimestampUTC($res);
        } catch(Exception $e) {
            $this->assertTrue(false, "Failed asserting that {$res} is a valid unix timestamp #3.");
        }

        $this->assertTrue(true);
    }
}

<?php

namespace Unimatrix\Cake\Test\TestCase\Shell\Helper;

use Cake\TestSuite\TestCase;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\Console\ConsoleIo;
use Cake\I18n\Time;
use Unimatrix\Cake\Shell\Helper\BenchmarkHelper;

class BenchmarkHelperTest extends TestCase
{
    protected $io;
    protected $now;
    protected $stub;
    protected $helper;

    public function setUp() {
        parent::setUp();
        $this->now = Time::getTestNow();
        $this->stub = new ConsoleOutput();
        $this->io = new ConsoleIo($this->stub);
        $this->helper = new BenchmarkHelper($this->io);
    }

    public function tearDown() {
        parent::tearDown();
        Time::setTestNow($this->now);
    }

    public function testInstant() {
        $delay = null;
        $now = new Time();
        $then = new Time($delay);

        $this->helper->start();
        $this->io->out($this->helper->started());
        Time::setTestNow($then);
        $this->helper->stop();
        $this->io->out($this->helper->ended());
        $this->io->out($this->helper->output());

        $this->assertSame([
            $now->i18nFormat('dd MMMM yyyy, h:mm a'),
            $then->i18nFormat('dd MMMM yyyy, h:mm a'),
            '0 seconds'
        ], $this->stub->messages());
    }

    public function testTook5Minutes() {
        $delay = '+5 minutes 15 seconds';
        $now = new Time();
        $then = new Time($delay);

        $this->helper->start();
        $this->io->out($this->helper->started());
        Time::setTestNow($then);
        $this->helper->stop();
        $this->io->out($this->helper->ended());
        $this->io->out($this->helper->output());

        $this->assertSame([
            $now->i18nFormat('dd MMMM yyyy, h:mm a'),
            $then->i18nFormat('dd MMMM yyyy, h:mm a'),
            '5 minutes and 15 seconds'
        ], $this->stub->messages());
    }

    public function testTookForever() {
        $delay = '+2 days 30 minutes 45 seconds';
        $now = new Time();
        $then = new Time($delay);

        $this->helper->start();
        $this->io->out($this->helper->started());
        Time::setTestNow($then);
        $this->helper->stop();
        $this->io->out($this->helper->ended());
        $this->io->out($this->helper->output());

        $this->assertSame([
            $now->i18nFormat('dd MMMM yyyy, h:mm a'),
            $then->i18nFormat('dd MMMM yyyy, h:mm a'),
            '2 days, 30 minutes and 45 seconds'
        ], $this->stub->messages());
    }
}

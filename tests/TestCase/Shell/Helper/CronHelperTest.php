<?php

namespace Unimatrix\Cake\Test\TestCase\Shell\Helper;

use Cake\TestSuite\TestCase;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\Console\ConsoleIo;
use Unimatrix\Cake\Shell\Helper\CronHelper;
use RuntimeException;

class CronHelperTest extends TestCase
{
    protected $io;
    protected $stub;
    protected $helper;

    public function setUp() {
        parent::setUp();
        $this->stub = new ConsoleOutput();
        $this->io = new ConsoleIo($this->stub);
        $this->helper = new CronHelper($this->io);
    }

    public function testAddJobWithoutMessage() {
        $this->expectException(RuntimeException::class);
        $this->helper->addJob();
    }

    public function testAddJobWithoutSchedule() {
        $this->expectException(RuntimeException::class);
        $this->helper->addJob(['msg' => 'testing cron message']);
    }

    public function testAddJobWithoutFunction() {
        $this->expectException(RuntimeException::class);
        $this->helper->addJob(['msg' => 'testing cron message', 'schedule' => 'testing cron schedule']);
    }

    public function testJobExistance() {
        $job = [
            'msg' => 'Test cron name',
            'schedule' => '* * * * *',
            'function' => function($msg = null) {
                return true;
            }
        ];
        $this->helper->addJob($job);

        $this->assertSame($job, $this->helper->getJob('Test cron name'));
        $this->assertSame([$job], $this->helper->getJobs());
    }

    public function testNoJobExecution() {
        $this->assertSame(0, $this->helper->output());
    }

    public function testFailedJobExecution() {
        $this->helper->addJob([
            'msg' => 'Test cron name',
            'schedule' => '* * * * *',
            'function' => function($msg = null) {
                return false;
            }
        ]);
        $this->assertSame(0, $this->helper->output());
    }

    public function testFutureJobExecution() {
        $this->helper->addJob([
            'msg' => 'Test cron name',
            'schedule' => '@yearly',
            'function' => function($msg = null) {
                return false;
            }
        ]);
        $this->assertSame(0, $this->helper->output());
    }

    public function testSuccessfullJobExecution() {
        $this->helper->addJob([
            'msg' => 'Test cron name',
            'schedule' => '* * * * *',
            'function' => function($msg = null) {
                return true;
            }
        ]);
        $this->assertSame(1, $this->helper->output());
    }

    public function testMixedJobExecution() {
        $this->helper->addJob([
            'msg' => 'Test cron name 1',
            'schedule' => '* * * * *',
            'function' => function($msg = null) {
                return true;
            }
        ]);
        $this->helper->addJob([
            'msg' => 'Test cron name 2',
            'schedule' => '* * * * *',
            'function' => function($msg = null) {
                return true;
            }
        ]);
        $this->helper->addJob([
            'msg' => 'Test cron name 3',
            'schedule' => '* * * * *',
            'function' => function($msg = null) {
                return false;
            }
        ]);
        $this->assertSame(2, $this->helper->output());
    }
}

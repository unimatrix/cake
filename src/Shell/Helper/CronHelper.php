<?php

namespace Unimatrix\Cake\Shell\Helper;

use RuntimeException;
use Cake\Console\Helper;
use Cron\CronExpression;

/**
 * Cron shell helper
 * Uses mtdowling/cron-expression to parse cron expressions and determine
 * if they are due to run and calculate the next or previous run dates
 *
 * Usage exmaple (in a shell -- src/shell)
 * ----------------------------------------------------------------
 * // add a job
 * $this->helper('Unimatrix/Cake.Cron')->addJob([
 *     'msg' => 'Test cron name',
 *     'schedule' => '* * * * *', // every time
 *     'function' => function($msg = null) {
 *         return true; // must return true if successfully ran
 *     }
 * ]);
 *
 * // execute crons
 * $ran = $this->helper('Unimatrix/Cake.Cron')->output();
 *
 *
 * @author Flavius
 * @version 1.0
 */
class CronHelper extends Helper
{
    /**
     * List of all cron jobs.
     * @var array [
     *     msg => 'Job message',
     *     schedule => 'Schedule',
     *     function => function () {} or array(object, function)
     * ]
     */
    private $jobs = [];

    /**
     * Load the cron jobs that should be runned and register them into the
     * jobs array.
     */
    public function addJob($job = []) {
        if(!isset($job['msg']))
            throw new RuntimeException('Job without a message');

        if(!isset($job['schedule']))
            throw new RuntimeException('Job without a CRON expression');

        if(!isset($job['function']))
            throw new RuntimeException('Job without a function');

        if($this->isDue($job['schedule']))
            $this->jobs[] = $job;
    }

    /**
     * Check if a cron job should be run.
     * The expression could be any cron expression or a predefined value:
     * - @yearly
     * - @annually
     * - @monthly
     * - @weekly
     * - @daily
     * - @hourly
     * ------------------------------
     *   *    *    *    *    *    *
     *   -    -    -    -    -    -
     *   |    |    |    |    |    |
     *   |    |    |    |    |    + year [optional]
     *   |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
     *   |    |    |    +---------- month (1 - 12)
     *   |    |    +--------------- day of month (1 - 31)
     *   |    +-------------------- hour (0 - 23)
     *   +------------------------- min (0 - 59)
     *
     * @param string $expr The cron expression
     * @return bool
     */
    private function isDue($expr) {
        $date = date('Y-m-d H:i');
        $time = strtotime($date);

        $cron = CronExpression::factory($expr);
        return ($time == $cron->getNextRunDate($date, 0, true)->getTimestamp());
    }

    /**
     * Return the list of jubs to run.
     * @return array
     */
    public function getJobs() {
        return $this->jobs;
    }

    /**
     * Get a particular job by msg
     * @param system $msg
     * @return boolean|array
     */
    public function getJob($msg) {
        $found = false;
        foreach($this->jobs as $job) {
            if($job['msg'] === $msg) {
                $found = $job;
                break;
            }
        }

        return $found;
    }

    /**
     * Load the cron jobs to run and execute them.
     * @return int Number of runned tasks
     */
    public function output($args = null) {
        $i = 0;
        foreach ($this->jobs as $job)
            if($job['function']($job['msg']) === TRUE)
                $i++;

        $this->jobs = [];
        return $i;
    }
}

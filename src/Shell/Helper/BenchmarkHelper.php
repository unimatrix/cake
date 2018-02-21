<?php

namespace Unimatrix\Cake\Shell\Helper;

use Cake\Console\Helper;

/**
 * Benchmark tool
 *
 * Usage exmaple (in a shell -- src/shell)
 * ----------------------------------------------------------------
 * // start benchmark
 * $benchmark = $this->helper('Unimatrix/Cake.Benchmark');
 * $benchmark->start();
 *
 * // console
 * $this->out('Started on ' . $benchmark->started());
 * $this->hr();
 *
 * // code goes here
 * // ------------------
 *
 * // ------------------
 *
 * // stop benchmark
 * $benchmark->stop();
 *
 * // console
 * $this->hr();
 * $this->out('Ended on ' . $benchmark->ended());
 * $this->out('Code execution took exactly ' . $benchmark->output());
 * ----------------------------------------------------------------
 *
 * @author Flavius
 * @version 1.0
 */
class BenchmarkHelper extends Helper
{
    // benchmark
    private $start = 0;
    private $stop = 0;

    /**
     * Set the start time
     */
    public function start() {
        $this->start = time();
    }

    /**
     * Started on
     * @param string $date
     * @return string
     */
    public function started($date = 'j F Y, g:i a') {
        return date($date, $this->start);
    }

    /**
     * Set the stop time
     */
    public function stop() {
        $this->stop = time();
    }

    /**
     * Ended on
     * @param string $date
     * @return string
     */
    public function ended($date = 'j F Y, g:i a') {
        return date($date, $this->stop);
    }

    /**
     * Calculate execution time
     * @return string
     */
    public function output($args = null) {
        // calculate stuff
        $delta = ($this->stop - $this->start);        
        $days = round(($delta % 604800) / 86400);
        $hours = round((($delta % 604800) % 86400) / 3600);
        $minutes = round(((($delta % 604800) % 86400) % 3600) / 60);
        $seconds = round((((($delta % 604800) % 86400) % 3600) % 60));

        // output stuff
        $msg = [];
        if($days > 0) $msg[] = $this->plural($days, 'd');
        if($hours > 0) $msg[] = $this->plural($hours, 'h');
        if($minutes > 0) $msg[] = $this->plural($minutes, 'm');
        $msg[] = $this->plural($seconds);

        // return stuff
        return $this->str_lreplace(',', ' ' . __d('Unimatrix/cake', 'and'), implode(', ', $msg));
    }

    /**
     * Translate time
     * @param int $c
     * @param string $o
     * @return string
     */
    private function plural($c, $o = 's') {
        $type = $o;
        if($o == 'd') $type = $c == 1 ? __d('Unimatrix/cake', 'day') : __d('Unimatrix/cake', 'days');
        if($o == 'h') $type = $c == 1 ? __d('Unimatrix/cake', 'hour') : __d('Unimatrix/cake', 'hours');
        if($o == 'm') $type = $c == 1 ? __d('Unimatrix/cake', 'minute') : __d('Unimatrix/cake', 'minutes');
        if($o == 's') $type = $c == 1 ? __d('Unimatrix/cake', 'second') : __d('Unimatrix/cake', 'seconds');

        return $c . ' ' . $type;
    }

    /**
     * Replace function
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    private function str_lreplace($search, $replace, $subject) {
        $pos = strrpos($subject, $search);
        if($pos !== false)
            $subject = substr_replace($subject, $replace, $pos, strlen($search));

        return $subject;
    }
}

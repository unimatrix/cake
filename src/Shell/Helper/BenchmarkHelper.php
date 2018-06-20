<?php

namespace Unimatrix\Cake\Shell\Helper;

use Cake\Console\Helper;
use Cake\I18n\Time;

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
 * @version 1.1
 */
class BenchmarkHelper extends Helper
{
    // benchmark
    private $begin;
    private $end;

    /**
     * Set the start time
     */
    public function start() {
        $this->begin = new Time();
    }

    /**
     * Started on
     * @param string $format
     * @return string
     */
    public function started($format = 'dd MMMM yyyy, h:mm a') {
        return $this->begin->i18nFormat($format);
    }

    /**
     * Set the stop time
     */
    public function stop() {
        $this->end = new Time();
    }

    /**
     * Ended on
     * @param string $format
     * @return string
     */
    public function ended($format = 'dd MMMM yyyy, h:mm a') {
        return $this->end->i18nFormat($format);
    }

    /**
     * Calculate execution time
     * @return string
     */
    public function output($args = null) {
        // calculate diff
        $diff = $this->end->diff($this->begin);

        // decide stuff
        $msg = [];
        if($diff->d > 0) $msg[] = $this->plural($diff->d, 'd');
        if($diff->h > 0) $msg[] = $this->plural($diff->h, 'h');
        if($diff->i > 0) $msg[] = $this->plural($diff->i, 'i');
        $msg[] = $this->plural($diff->s);

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
        if($o == 'i') $type = $c == 1 ? __d('Unimatrix/cake', 'minute') : __d('Unimatrix/cake', 'minutes');
        if($o == 's') $type = $c == 1 ? __d('Unimatrix/cake', 'second') : __d('Unimatrix/cake', 'seconds');

        return "{$c} {$type}";
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

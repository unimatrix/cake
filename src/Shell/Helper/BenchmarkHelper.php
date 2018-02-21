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
        $delta_T = ($this->stop - $this->start);
        $day = round(($delta_T % 604800) / 86400);
        $hours = round((($delta_T % 604800) % 86400) / 3600);
        $minutes = round(((($delta_T % 604800) % 86400) % 3600) / 60);
        $sec = round((((($delta_T % 604800) % 86400) % 3600) % 60));

        // output stuff
        $msg = [];
        if($day > 0) $msg[] = $this->pluralize($day, 'day');
        if($hours > 0) $msg[] = $this->pluralize($hours, 'hour');
        if($minutes > 0) $msg[] = $this->pluralize($minutes, 'minute');
        $msg[] = $this->pluralize($sec, 'second');

        // return stuff
        return $this->str_lreplace(',', ' and', implode(', ', $msg));
    }

    /**
     * Pluralize function
     * @param int $count
     * @param string $text
     * @return string
     */
    private function pluralize($count, $text) {
        return $count . (($count == 1) ? (" $text") : (" {$text}s"));
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

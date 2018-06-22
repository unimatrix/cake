<?php

namespace Unimatrix\Cake\View\Helper;

use Cake\View\Helper;

/**
 * Debug
 * Contains useful debug functions
 *
 * Load:
 * ---------------------------------------------------
 * This helper must be loaded in your View/AppView.php
 * before you can use it
 *
 * $this->loadHelper('Unimatrix/Cake.Debug');
 *
 * Usage:
 * ---------------------------------------------------
 * $this->Number->precision($this->Debug->requestTime() * 1000, 0)
 *
 * @author Flavius
 * @version 1.1
 */
class DebugHelper extends Helper
{
    /**
     * Get the total execution time until this point
     *
     * @return float elapsed time in seconds since script start.
     */
    public static function requestTime() {
        $start = self::requestStartTime();
        $now = microtime(true);

        return ($now - $start);
    }

    /**
     * Get the time the current request started.
     *
     * @param bool $test Stub to help test the code by avoiding the constant
     * @return float time of request start
     */
    public static function requestStartTime($test = false) {
        if(defined('TIME_START') && $test === false) $startTime = TIME_START;
        elseif(isset($GLOBALS['TIME_START'])) $startTime = $GLOBALS['TIME_START'];
        else $startTime = env('REQUEST_TIME');

        return $startTime;
    }
}

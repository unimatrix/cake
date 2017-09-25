<?php

namespace Unimatrix\Cake\Lib;

/**
 * Helpers
 * Contains helper functions
 *
 * @author Flavius
 * @version 2.0
 */
class Misc {
    // pre style
    public static $pre = [
        'font-family: monospace',  'white-space: pre', 'margin: 1em',
        'clear: both', 'background: #F5F5F5', 'border: 1px solid brown',
        'padding: 10px', 'position: relative', 'z-index: 9999',
        'font-size: 13px', 'line-height: 16px', 'text-align: left',
        'text-shadow: none', 'color: #000'
    ];

    // title style
    public static $title = [
        'color: brown', 'font-family: Verdana', 'font-size: 16px',
        'display: block', 'padding-bottom: 2px', 'margin-bottom: 10px',
        'border-bottom: 1px solid brown'
    ];

    /**
     * Makes the debug pretty for output
     * also adds the option to place a title :)
     *
     * @param unknown $var
     * @param string $title
     * @param bool $return
     */
    public static function dump($var, $title = false, $return = false) {
        // capture on return
        if($return)
            ob_start();

        // start pre, got title?
        echo '<pre style="'. implode(';', self::$pre) .'">';
        if($title)
            echo '<span style="'. implode(';', self::$title) .'">'. $title .'</span>';

        // do the deed and end pre
        var_dump($var);
        echo "</pre>";

        // return output
        if($return)
            return ob_get_clean();
    }
}

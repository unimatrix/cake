<?php

namespace Unimatrix\Cake\Lib;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Helpers
 * Contains helper functions
 *
 * @author Flavius
 * @version 1.0
 */
class Misc {
    /**
     * Wrapper for symphony's dumper,
     * also adds the option to place a title :)
     *
     * @param unknown $var
     * @param string $title
     * @param bool $return
     */
    public static function dump($var, $title = false, $return = false) {
        // init symfony classes
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();

        // got title?
        $h1 = null;
        if($title) {
            $style = implode(';', [
                'font: bold 25px/30px Tahoma', 'color: #e0115f',
                'border-bottom: 2px solid #e0115f', 'background: #18171B',
                'margin: 0px 0px -12px 0px', 'padding: 5px'
            ]);
            $h1 = "<h1 style='{$style}'>{$title}</h1>";
        }

        // dumper
        $dumper = $h1 . $dumper->dump($cloner->cloneVar($var), true);

        // return or output
        if($return) return $dumper;
        else echo $dumper;
    }
}

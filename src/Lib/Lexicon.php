<?php

namespace Unimatrix\Cake\Lib;

/**
 * Lexicon
 * Contains text functions for romanian characters
 *
 * @author Flavius
 * @version 1.0
 */
class Lexicon {
    // regex syntax used (case insensitive, unicode)
    private static $re = '/(%s)/iu';
    private static $ro = ['a' => '[aăâ]', 's' => '[sșş]', 't' => '[tțţ]', 'i' => '[iî]'];

    /**
     * Match romanian regex
     * @param string $keyword
     * @param string $text
     * @param array matches
     * @return bool
     */
    public static function match($keyword, $text = '', &$matches = false) {
        return (bool)preg_match(sprintf(self::$re, self::regex($keyword)), $text, $matches, PREG_OFFSET_CAPTURE);
    }

    /**
     * Highlight word
     * @param string $text
     * @param string|array $phrase
     */
    public static function highlight($text, $phrase) {
        if(empty($phrase))
            return $text;

        if(is_string($phrase))
            $phrase = [$phrase];

        foreach($phrase as $idx => $one)
            $phrase[$idx] = self::regex($one);

        return preg_replace(sprintf(self::$re, implode('|', $phrase)), '<span class="highlight">\1</span>', $text);
    }

    /**
     * Handle romanian diacritics
     * @param string $keyword
     */
    private static function regex($keyword = null) {
        return strtr($keyword, self::$ro);
    }

    /**
     * Cut text left middle right
     * @param string or array $value
     * @param integer $length
     * @param string $ellipsis
     */
    public static function cuttext($value, $length = 200, $ellipsis = '...') {
        if(!is_array($value)) {
            $string = $value;
            $match_to = $value{0};
        } else list($string, $match_to) = $value;

        self::match($match_to, $string, $matches);
        $match_start = isset($matches[0]) && isset($matches[0][1]) ? mb_substr($string, mb_strlen(substr($string, 0, $matches[0][1]))) : false;
        $match_compute = $match_start ? (mb_strlen($string) - mb_strlen($match_start)) : 0;

        if(mb_strlen($string) > $length) {
            if($match_compute < ($length - mb_strlen($match_to))) {
                $pre_string = mb_substr($string, 0, $length);
                $pos_end = mb_strrpos($pre_string, ' ');
                if($pos_end === false) $string = trim($pre_string).$ellipsis;
                else $string = trim(mb_substr($pre_string, 0, $pos_end)).$ellipsis;
            } else if($match_compute > (mb_strlen($string) - ($length - mb_strlen($match_to)))) {
                $pre_string = mb_substr($string, (mb_strlen($string) - ($length - mb_strlen($match_to))));
                $pos_start = mb_strpos($pre_string, ' ');
                if($pos_start === false) $string = $ellipsis.trim($pre_string);
                else $string = $ellipsis.trim(mb_substr($pre_string, $pos_start));
            } else {
                $pre_string = mb_substr($string, ($match_compute - round(($length / 3))), $length);
                $pos_start = mb_strpos($pre_string, ' ');
                if($pos_start === false) $pre_string = $ellipsis.trim($pre_string);
                else $pre_string = $ellipsis.trim(mb_substr($pre_string, $pos_start));
                $pos_end = mb_strrpos($pre_string, ' ');
                if($pos_end === false) $pre_string = trim($pre_string).$ellipsis;
                else $pre_string = trim(mb_substr($pre_string, 0, $pos_end)).$ellipsis;
                $string = $pre_string;
            }
        }

        return trim($string);
    }
}

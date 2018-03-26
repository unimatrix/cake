<?php

namespace Unimatrix\Cake\View\Helper;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Utility\Inflector;
use Cake\Core\Exception\Exception;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\PathConverter\Converter;
use voku\helper\HtmlMin as HTML;

// Minify libraries used
class Algorithms
{
    public static function css($input = null, array $options = []) {
        $css = new CSS($input);
        return trim($css->minify());
    }

    public static function js($input = null, array $options = []) {
        $js = new JS($input);
        return trim($js->minify());
    }

    public static function html($input = null, array $options = []) {
        $html = new HTML();
        foreach($options as $option => $value)
            $html->$option($value);

        return trim($html->minify($input));
    }
}

/**
 * Minify
 * Will minify HTML
 * Will combine and minify JS and CSS files (if not minified already)
 *
 * Load:
 * ---------------------------------------------------
 * This helper must be loaded in your View/AppView.php
 * before you can use it. Default values below:
 *
 * $this->loadHelper('Unimatrix/Cake.Minify', [
 *     'compress' => [
 *         'html' => true,
 *         'css' => true,
 *         'js' => true
 *     ],
 *     'config' => [
 *         'html' => [ // see voku/HtmlMin for options
 *             'doRemoveOmittedHtmlTags' => false
 *         ],
 *         'css' => [],
 *         'js' => []
 *     ],
 *     'paths' => [
 *         'css' => '/cache-css',
 *         'js' => '/cache-js'
 *     ]
 * ]);
 *
 * Usage:
 * ---------------------------------------------------
 * $this->Minify->style('your-css-file'); // or array
 * $this->Minify->fetch('style');
 *
 * $this->Minify->script('your-script'); // or array
 * $this->Minify->fetch('script');
 *
 * Simulate live in development:
 * ---------------------------------------------------
 * $this->Minify->fetch('style', true);
 *
 * Inline usage:
 * ---------------------------------------------------
 * Note: also accepts second argument to return the minified code instead of echoing it
 * $this->Minify->inline('script', "
 *     var SUPERGLOBAL = 'free willy';
 * ");
 *
 * @author Flavius
 * @version 1.7
 */
class MinifyHelper extends Helper {
    // load html and url helpers
    public $helpers = ['Html', 'Url'];

    // default config
    protected $_defaultConfig = [
        'compress' => [
            'html' => true,
            'css' => true,
            'js' => true
        ],
        'config' => [
            'html' => [],
            'css' => [],
            'js' => []
        ],
        'paths' => [
            'css' => '/cache-css',
            'js' => '/cache-js'
        ]
    ];

    // container for css and js files
    private $css = [
        'full' => null,
        'intern' => [],
        'extern' => []
    ];
    private $js = [
        'full' => null,
        'intern' => [],
        'extern' => []
    ];

    // keep a reference to avoid duplicates
    private $inline = [
        'css' => [],
        'js' => []
    ];

    // compress flag
    private $compress = false;

    /**
     * Constructor
     * @param View $View
     * @param array $config
     */
    public function __construct(View $View, array $config = []) {
        // call parent constructor
        parent::__construct($View, $config);

        // fix trailing slash
        $this->_config['paths']['css'] = rtrim($this->_config['paths']['css'], '/');
        $this->_config['paths']['js'] = rtrim($this->_config['paths']['js'], '/');

        // calculate full system path
        $this->css['full'] = rtrim(Configure::read('App.wwwRoot'), DS) . str_replace('/', DS, $this->_config['paths']['css']);
        $this->js['full'] = rtrim(Configure::read('App.wwwRoot'), DS) . str_replace('/', DS, $this->_config['paths']['js']);
    }

    /**
     * HTML compressor
     * @see Helper::afterLayout()
     */
     public function afterLayout() {
         // run through algorithm
         $compressed = $this->_html($this->getView()->Blocks->get('content'));

         // set html content
         $this->getView()->Blocks->set('content', $compressed);
     }

    /**
     * Add css files to list
     * @param array $files
     * @return bool
     */
    public function style($files = null) {
        // organize files and return if successfull or not
        return $this->organize('css', $files);
    }

    /**
     * Add js files to list
     * @param array $files
     * @return bool
     */
    public function script($files = null) {
        // organize files and return if successfull or not
        return $this->organize('js', $files);
    }

    /**
     * Fetch either combined css or js
     * @param string $what style | script
     * @param bool $live
     * @throws Exception
     */
    public function fetch($what = null, $live = false) {
        // not supported?
        if(!in_array($what, ['style', 'script']))
            throw new Exception("{$what} not supported");

        // compress?
        $this->compress = !Configure::read('debug') || $live == true;

        // call private function
        $function = '_' . $what;
        echo $this->$function();
    }

    /**
     * Fetch inline minified css or js
     * @param string $what style | script
     * @param string $data text that needs to be minified inline
     * @param bool $return should we return or echo the minified data?
     * @throws Exception
     */
    public function inline($what = null, $data = null, $return = false) {
        // not supported?
        if(!in_array($what, ['style', 'script']))
            throw new Exception("{$what} not supported");

        // call private function
        $function = '_inline' . ucfirst($what);
        $data = $this->$function($data);

        // return or output?
        if($return) return $data;
        echo $data;
    }

    /**
     * Organize into internal / external array
     * @param string $what js | css
     * @param array $files
     * @throws Exception
     * @return bool
     */
    private function organize($what = null, $files = null) {
        // not supported?
        if(!in_array($what, ['css', 'js']))
            throw new Exception("{$what} not supported");

        // nothing?
        if(is_null($files))
            return false;

        // string? convert to array
        if(is_string($files))
            $files = [$files];

        // not array?
        if(!is_array($files))
            return false;

        // unique check (first pass)
        $files = array_unique($files);

        // add each file to group with www_root
        $intern = [];
        foreach($files as $idx => $file) {
            // unique check (second pass)
            $existing = array_search($file, $this->$what['extern']);
            if($existing !== false) {
                unset($this->$what['intern'][$existing]);
                unset($this->$what['extern'][$existing]);
            }

            // get system path, if file doesn't exist, remove from array
            $path = $this->path($file, ['pathPrefix' => Configure::read("App.{$what}BaseUrl"), 'ext' => '.' . $what]);
            if(!$path) {
                unset($files[$idx]);
                continue;

            // file exists? add to intern
            } else $intern[] = $path;
        }

        // array merge
        $this->$what['intern'] = array_merge($intern, $this->$what['intern']);
        $this->$what['extern'] = array_merge($files, $this->$what['extern']);

        // files successfully added
        return true;
    }

    /**
     * Get full webroot path for an asset
     * @param string $file
     * @param array $options
     * @return string | bool
     */
    private function path($file, array $options = []) {
        // get base and full paths
        $base = $this->Url->assetUrl($file, $options);
        $fullpath = preg_replace('/^' . preg_quote($this->request->getAttribute('webroot'), '/') . '/', '', urldecode($base));

        // do webroot path
        $webrootPath = Configure::read('App.wwwRoot') . str_replace('/', DS, $fullpath);
        if(file_exists($webrootPath))
            return $webrootPath;

        // do plugin webroot path
        $parts = [];
        $segments = explode('/', $fullpath);
        for($i = 0; $i < 2; $i++) {
            if(!isset($segments[$i]))
                break;

            $parts[] = Inflector::camelize($segments[$i]);
            $plugin = implode('/', $parts);

            if($plugin && Plugin::loaded($plugin)) {
                $segments = array_slice($segments, $i + 1);
                $pluginWebrootPath = str_replace('/', DS, Plugin::path($plugin)) . 'webroot' . DS . implode(DS, $segments);
                if(file_exists($pluginWebrootPath))
                    return $pluginWebrootPath;
            }
        }

        // not found?
        return false;
    }

    /**
     * Attempt to create the filename for the selected resources
     * @param string $what js | css
     * @throws Exception
     * @return string|bool
     */
    private function filename($what = null) {
        // not supported?
        if(!in_array($what, ['css', 'js']))
            throw new Exception("{$what} not supported");

        // no files?
        if(!$this->$what['intern'])
            return false;

        $last = 0;
        foreach($this->$what['intern'] as $res)
            if(file_exists($res))
                $last = max($last, filemtime($res));

        return "cache-{$last}-" . md5(serialize($this->$what['intern'])) . ".{$what}";
    }

    /**
     * Take individual files and process them based on an algorithm
     * @param string $what js | css
     * @throws Exception
     * @return string
     */
    private function process($what = null) {
        // not supported?
        if(!in_array($what, ['css', 'js']))
            throw new Exception("{$what} not supported");

        // go through each file
        $output = null;
        foreach($this->$what['intern'] as $idx => $file) {
            // get file contents
            $contents = file_get_contents($file);

            // css? fix paths
            if($what == 'css')
                $contents = $this->dilemma($contents, $this->$what['extern'][$idx]);

            // not compressed? run through algorithms
            if(strpos($file, ".min.{$what}") === false) {
                $contents = Algorithms::$what($contents, $this->_config['config'][$what]);

                // add script delimiter if js
                if($what == 'js')
                    $contents .= ";";
            }

            // add to output
            $output .= "\n" . $contents . "\n";
        }

        // strip newlines
        $output = preg_replace('#(\r\n?|\n){2,}#', "\n", $output);

        // return the compressed string
        return trim($output);
    }

    /**
     * Dilemma?
     * This function uses morph to fix and convert paths found in css
     *
     * @param string $input
     * @param bool $from
     * @return string
     */
    private function dilemma($input = null, $from = false) {
        // calculate from and to
        list($plugin, $from) = $this->_View->pluginSplit($from, false);
        $from = Configure::read('App.cssBaseUrl') . $from;
        if(isset($plugin))
            $from = Inflector::underscore($plugin) . '/' . $from;

        // start converter
        $converter = new Converter("/{$from}-fake-file.css", "{$this->_config['paths']['css']}/cache-fake-file.css");

        // fix paths
        return $this->morph($input, function($url) use($converter) {
            return $converter->convert($url);
        });
    }

    /**
     * Morph
     * The function that detects urls and morphs paths accordingly
     *
     * @param string $input
     * @param callable $callback
     * @param string $ignore
     * @return mixed
     */
    private function morph($input, callable $callback, $ignore = '/^(data:|https?:|\\/)/') {
        // define regex
        $relativeRegexes = [
            '/url\(\s*(?P<quotes>["\'])?(?P<path>.+?)(?(quotes)(?P=quotes))\s*\)/ix',
            '/@import\s+(?P<quotes>["\'])(?P<path>.+?)(?P=quotes)/ix',
        ];

        // find all relative urls in css
        $matches = [];
        foreach($relativeRegexes as $relativeRegex)
            if(preg_match_all($relativeRegex, $input, $regexMatches, PREG_SET_ORDER))
                $matches = array_merge($matches, $regexMatches);

        // start empty
        $search = [];
        $replace = [];

        // loop all urls
        foreach ($matches as $match) {
            // determine if it's a url() or an @import match
            $type = (strpos($match[0], '@import') === 0 ? 'import' : 'url');
            $url = $match['path'];

            // ignore transformation?
            if(preg_match($ignore, $url) === 0) {
                // attempting to interpret GET-params makes no sense, so let's discard them for awhile
                $params = strrchr($url, '?');
                $url = $params ? substr($url, 0, -strlen($params)) : $url;

                // fix relative url
                $url = $callback($url);

                // now that the path has been converted, re-apply GET-params
                $url .= $params;
            }

            // urls with control characters above 0x7e should be quoted.
            $url = trim($url);
            if(preg_match('/[\s\)\'"#\x{7f}-\x{9f}]/u', $url))
                $url = $match['quotes'] . $url . $match['quotes'];

            // build replacement
            $search[] = $match[0];
            if($type === 'url')
                $replace[] = 'url('.$url.')';
            elseif($type === 'import')
                $replace[] = '@import "'.$url.'"';
        }

        // return replaced input
        return str_replace($search, $replace, $input);
    }

    /**
     * HTML compressor
     * @param string $content
     * @return string
     */
    private function _html($content) {
        // compress?
        if($this->_config['compress']['html'])
            $content = Algorithms::html($content, $this->_config['config']['html']);

        // return content
        return $content;
    }

    /**
     * Create the cache file if it doesnt exist
     * Return the combined css either compressed or not (depending on the setting)
     */
    private function _style() {
        // we need to compress?
        if($this->compress && $this->_config['compress']['css']) {
            // get cache
            $cache = $this->filename('css');
            if(!$cache)
                return false;

            // get paths
            $web_path = $this->_config['paths']['css'] . '/' . $cache;
            $system_path = $this->css['full'] . DS . $cache;

            // no cache file? write it
            if(!file_exists($system_path)) {
                // process compression
                $output = $this->process('css');

                // write to file
                file_put_contents($system_path, $output);
            }

            // output with the HTML helper
            return $this->Html->css($web_path);

        // no need to compress? output separately with the HTML helper
        } else return $this->Html->css($this->css['extern']);
    }

    /**
     * Create the cache file if it doesnt exist
     * Return the combined js either compressed or not (depending on the setting)
     */
    private function _script() {
        // we need to compress?
        if($this->compress && $this->_config['compress']['js']) {
            // get cache
            $cache = $this->filename('js');
            if(!$cache)
                return false;

            // get paths
            $web_path = $this->_config['paths']['js'] . '/' . $cache;
            $system_path = $this->js['full'] . DS . $cache;

            // no cache file? write it
            if(!file_exists($system_path)) {
                // process compression
                $output = $this->process('js');

                // write to file
                file_put_contents($system_path, $output);
            }

            // output the cached file with the HTML helper
            return $this->Html->script($web_path);

        // no need to compress? output separately with the HTML helper
        } else return $this->Html->script($this->js['extern']);
    }

    /**
     * Return the compressed inline css data
     * @param string $data
     */
    private function _inlineStyle($data = null) {
        // no data?
        if(is_null($data))
            return false;

        // fix paths
        $data = $this->morph($data, function($url) {
            return $this->Url->build($url);
        }, '/^(data:|https?:)/');

        // compress
        $data = Algorithms::css($data, $this->_config['config']['css']);

        // keep a reference to avoid duplicates
        $hash = md5($data);
        if(in_array($hash, $this->inline['css']))
            return false;
        else $this->inline['css'][] = $hash;

        // output
        return "<style>{$data}</style>";
    }

    /**
     * Return the compressed inline js data
     * @param string $data
     */
    private function _inlineScript($data = null) {
        // no data?
        if(is_null($data))
            return false;

        // compress
        $data = Algorithms::js($data, $this->_config['config']['js']);

        // keep a reference to avoid duplicates
        $hash = md5($data);
        if(in_array($hash, $this->inline['js']))
            return false;
        else $this->inline['js'][] = $hash;

        // output
        return "<script>{$data}</script>";
    }
}

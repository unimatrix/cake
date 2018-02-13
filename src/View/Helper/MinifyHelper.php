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
use voku\helper\HtmlMin as HTML;

// Minify libraries used
class Algorithms
{
    public static function css($input = null) {
        return trim((new CSS($input))->minify());
    }

    public static function js($input = null) {
        return trim((new JS($input))->minify());
    }

    public static function html($input = null) {
        $html = new HTML();
        $html->doRemoveSpacesBetweenTags(true);

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
 * @version 1.4
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
     * @param unknown $settings
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
        $function = '_inline_' . $what;
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
     * @return string
     */
    private function filename($what = null) {
        // not supported?
        if(!in_array($what, ['css', 'js']))
            throw new Exception("{$what} not supported");

        $last = 0;
        foreach($this->$what['intern'] as $res)
            if(file_exists($res))
                $last = max($last, filemtime($res));

        return "cache-{$last}-" . md5(serialize($this->$what['intern'])) . ".{$what}";
    }

    /**
     * Transform relative paths (../) to absolute ones
     * - also fix unimatrix css paths for cake instalations in subdirectories
     *
     * @param string $input
     * @return string
     */
    private function absolute($input = null) {
        $input = str_replace('/unimatrix/', '../unimatrix/', $input);
        return preg_replace('/(\.\.\/)+/i', $this->Url->build('/', true), $input);
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
        foreach($this->$what['intern'] as $file) {
            // get file contents
            $contents = file_get_contents($file);

            // not compressed? run through algorithms
            if(strpos($file, ".min.{$what}") === false) {
                $contents = Algorithms::$what($contents);

                // add script delimiter if js
                if($what == 'js')
                    $contents .= ";";
            }

            // add to output
            $output .= "\n" . $contents . "\n";
        }

        // strip newlines
        $output = preg_replace('#(\r\n?|\n){2,}#', "\n", $output);

        // css? replace relative paths to absolute paths
        if($what == 'css')
            $output = $this->absolute($output);

        // return the compressed string
        return trim($output);
    }

    /**
     * HTML compressor
     * @param string $content
     * @return string
     */
    private function _html($content) {
        // compress?
        if($this->_config['compress']['html'])
            $content = Algorithms::html($content);

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
            // get paths
            $cache = $this->filename('css');
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
            // get paths
            $cache = $this->filename('js');
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
    private function _inline_style($data = null) {
        // no data?
        if(is_null($data))
            return false;

        // replace relative paths to absolute paths
        $data = $this->absolute($data);

        // compress
        $data = Algorithms::css($data);

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
    private function _inline_script($data = null) {
        // no data?
        if(is_null($data))
            return false;

        // compress
        $data = Algorithms::js($data);

        // keep a reference to avoid duplicates
        $hash = md5($data);
        if(in_array($hash, $this->inline['js']))
            return false;
        else $this->inline['js'][] = $hash;

        // output
        return "<script>{$data}</script>";
    }
}

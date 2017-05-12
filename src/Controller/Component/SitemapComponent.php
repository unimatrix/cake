<?php

namespace Unimatrix\Cake\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\Utility\Xml;
use Cake\Utility\Inflector;
use Cake\I18n\Time;

/**
 * Sitemap component
 * Basic to Advanced Sitemap implementation
 *
 * Example of routes configured for sitemap
 * --------------------------------------------------
 * $routes->connect('/', ['controller' => 'Index', 'action' => 'index'], ['sitemap' => ['modified' => time(), 'frequency' => 'daily', 'priority' => '1.0']]);
 * $routes->connect('/page', ['controller' => 'Page', 'action' => 'display'], ['sitemap' => ['modified' => time(), 'frequency' => 'monthly', 'priority' => '0.5']]);
 *
 * The options are as follows: [
 *     'modified' => time(), // must be unixtime
 *     'frequency' => 'daily',
 *     'priority' => '1.0'
 * ]
 *
 * If you ommit the modified key it will calculate the file modified time from the template file.
 * (This will not work properly if you change the render file from inside action)
 *
 * Other options for 'modified' are 'ctrl' and 'callback'
 * - ctrl: It will take the modified time from the controller in conjuction with the template file and return the latest modified time from the two
 * - callback: It will attempt to call the method `index_sitemap` from the controller where you can calculate your own modified time. EG:
 * --------------------------------------------------
 * public function index_sitemap() {
 *     // get last modified time (use data from model or whatever custom code you want)
 *     $time = ..........
 *
 *     // return
 *     return $time ? $time : false;
 * }
 * --------------------------------------------------
 * Can also return false, if false time() will be used automatically.
 *
 * ALSO
 *
 * In case your route is dynamic (if it contains `*` or `:slug`, EG: $routes->connect('/pages/*', ...), $routes->connect('/pages/:slug', ...)
 * SitemapComponent will attempt to call the method `dynamic_sitemap` from the controller where you can calculate your own urls and modified time. EG:
 * --------------------------------------------------
 * public function dynamic_sitemap() {
 *     // get articles (from a model or whatever)
 *     $news = ....................
 *
 *     // format
 *     $out = [];
 *     foreach($news as $article)
 *         $out[] = [
 *             'url' => Router::url(['controller' => 'Pages', 'action' => 'view', $article['slug']], true),
 *             'modified' => $article['modified']->toAtomString(),
 *         ];
 *
 *     // return
 *     return $out;
 * }
 * --------------------------------------------------
 *
 * @author Flavius
 * @version 0.1
 */
class SitemapComponent extends Component
{
    // Load request handler
    public $components = ['RequestHandler'];

    // the exclusion array
    public $exclude = [];

    // url storage
    protected $_url = [];

    // sitemap root
    protected $_root = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="%s"?><urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';

    /**
     * Render sitemap
     * @return string as xml
     */
    public function render() {
        // respond as xml
        $this->RequestHandler->respondAs('xml');

        // start xml
        $xml = Xml::build(sprintf($this->_root, $this->_path('Unimatrix/Cake.xsl/sitemap.xsl')));

        // get urls from router
        $this->_router();

        // append urls
        $this->_append($xml, Xml::fromArray(['urlset' => ['url' => $this->_url]]));

        // return xml
        return $xml->asXML();
    }

    /**
     * Get urls from Router
     */
    protected function _router() {
        // go through each defined route
        foreach(Router::routes() as $route) {
            // not supposed to be in sitemap? exclude
            if(!isset($route->options['sitemap']))
                continue;

            // defaults
            $changefreq = $route->options['sitemap']['frequency'] ?? 'monthly';
            $priority = $route->options['sitemap']['priority'] ?? '0.6';

            // dynamic? execute callback if it exists
            if(strpos($route->template, '*') !== false || strpos($route->template, ':slug') !== false) {
                $callback = $this->_callback($route, true);
                if($callback)
                    if(is_array($callback))
                        foreach($callback as $item)
                            $this->_url[] = [
                                'loc' => $item['url'],
                                'lastmod' => $item['modified'],
                                'changefreq' => $changefreq,
                                'priority' => $priority
                            ];

                // ignore "/*"
                continue;
            }

            // in exclude array?
            if(in_array($route->template, $this->exclude))
                continue;

            // calculate lastmod
            if(isset($route->options['sitemap']['modified'])) {
                switch($route->options['sitemap']['modified']) {
                    case 'callback': $lastmod = $this->_callback($route); break;
                    case 'ctrl': $lastmod = $this->_ctrl($route); break;
                    default: $lastmod = $route->options['sitemap']['modified'];
                }
            } else $lastmod = $this->_file($route);
            $lastmod = (new Time($lastmod ?: time()))->timezone(date_default_timezone_get())->toAtomString();

            // add record
            $this->_url[] = [
                'loc' => Router::url($route->template, true),
                'lastmod' => $lastmod,
                'changefreq' => $changefreq,
                'priority' => $priority
            ];
        }
    }

    /**
     * Perform callback
     * @param object $route
     * @param bool $dynamic
     * @return integer for index, array for dynamic or false on failure
     */
    protected function _callback($route, $dynamic = false) {
        // default variables
        $class = "App\Controller\\{$route->defaults['controller']}Controller";
        $object = new $class();
        $method = ($dynamic ? 'dynamic' : 'index') . '_sitemap';

        // return
        return method_exists($object, $method) ? call_user_func([$object, $method], $route) : false;
    }

    /**
     * Get file modified time from a template file
     * @param object $route
     * @return integer or false on failure
     */
    protected function _file($route) {
        // read the template path
        $template = Configure::read('App.paths.templates');

        // read file modified time
        $file = reset($template) . $route->defaults['controller'] . DS . $route->defaults['action'] . '.ctp';
        return file_exists($file) ? filemtime($file) : false;
    }

    /**
     * Get file modified time from the controller file
     * @param object $route
     * @param bool $file Use template file too?
     * @return integer or false on failure
     */
    protected function _ctrl($route, $file = true) {
        // read ctrl file modified time
        $file = APP . 'Controller' . DS . $route->defaults['controller'] . 'Controller.php';
        $ctrl = file_exists($file) ? filemtime($file) : false;
        $ctp = $file ? $this->_file($route) : false;
        return max([$ctrl, $ctp]);
    }

    /**
     * Generate URL for given asset file
     * @param string $asset
     * @return string
     */
    protected function _path($asset) {
        list($plugin, $path) = pluginSplit($asset, false);
        $path = Inflector::underscore($plugin) . '/' . $path;

        return Router::url('/'. $path, true);
    }

    /**
     * SimpleXML helper, append 2 objects together
     * @param SimpleXML $to
     * @param SimpleXML $from
     */
    protected function _append(&$to, $from) {
        // go through each kid
        foreach($from->children() as $child) {
            $temp = $to->addChild($child->getName(), (string) $child);
            foreach($child->attributes() as $key => $value)
                $temp->addAttribute($key, $value);

            // perform append
            $this->_append($temp, $child);
        }
    }
}

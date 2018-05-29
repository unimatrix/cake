<?php

namespace Unimatrix\Cake\Controller\Component;

use DateTime;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;
use Cake\Controller\Component;

/**
 * Cookie Component for cake 3.5+
 *
 * @author Flavius
 * @version 1.0
 */
class CookieComponent extends Component
{
    // controller and cookies
    private $ctrl;
    private $cookies;

    // the cache
    protected $cache = [];

    /**
     * Default config.
     * @var array
     */
    protected $_defaultConfig = [
        'value' => '',
        'expire' => null,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httpOnly' => false
    ];

    /**
     * {@inheritDoc}
     * @see \Cake\Controller\Component::initialize()
     */
    public function initialize(array $config) {
        parent::initialize($config);

        // set request
        $this->ctrl = $this->getController();
        $this->cookies = (new CookieCollection())->createFromServerRequest($this->ctrl->getRequest());
    }

    /**
     * Check if the cookie exists
     *
     * @param string $name The name of the cookie.
     * @return boolean
     */
    public function check($name) {
        return $this->cookies->has($name);
    }

    /**
     * Read cookie
     *
     * @param string $name The name of the cookie.
     * @param string $path Path to read the data from
     * @return NULL|string
     */
    public function read($name, $path = null) {
        // in cache?
        if(isset($this->cache[$name]))
            return !is_null($path) && isset($this->cache[$name][$path]) ? $this->cache[$name][$path] : $this->cache[$name];

        // no cookie?
        if(!$this->check($name))
            return null;

        return $this->cookies->get($name)->read($path);
    }

    /**
     * Write cookie
     *
     * @param string $name The name of the cookie.
     * @param string|array $value Value of the cookie to set
     * @param array $config The configuration for the cookie
     */
    public function write($name, $value, $config = []) {
        // handle config
        $options = $config + $this->_config;

        // write to cache
        $this->cache[$name] = $value;

        // create cookie
        $cookie = (new Cookie($name))
            ->withValue($value)
            ->withPath($options['path'])
            ->withDomain($options['domain'])
            ->withSecure($options['secure'])
            ->withHttpOnly($options['httpOnly']);

        // handle expire
        if($options['expire'])
            $cookie->withExpiry(new DateTime($options['expire']));

        // send with response
        $this->ctrl->setResponse($this->ctrl->getResponse()->withCookie($cookie));
    }

    /**
     * Delete cookie
     *
     * @param string $name The name of the cookie.
     * @param array $config The configuration for the cookie
     */
    public function delete($name, $config = []) {
        // handle config
        $options = $config + $this->_config;

        // delete from cache
        if(isset($this->cache[$name]))
            unset($this->cache[$name]);

        // create cookie
        $cookie = (new Cookie($name))
            ->withValue('')
            ->withPath($options['path'])
            ->withDomain($options['domain'])
            ->withSecure($options['secure'])
            ->withHttpOnly($options['httpOnly']);

        // send with response
        $this->ctrl->setResponse($this->ctrl->getResponse()->withExpiredCookie($cookie));
    }
}

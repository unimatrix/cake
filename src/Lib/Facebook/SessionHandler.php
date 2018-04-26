<?php

namespace Unimatrix\Cake\Lib\Facebook;

use Facebook\PersistentData\PersistentDataInterface;

/**
 * Cake Session Persistent Data Handler
 * Enables facebook-php-sdk (facebook/facebook-php-sdk-v4) to use the correct cake session objects
 *
 * Usage example:
 * ---------------------------------
 * // controller
 * use Unimatrix\Cake\Lib\Facebook\SessionHandler;
 * use Facebook;
 *
 * $this->facebook = new Facebook\Facebook([
 *     'app_id' => $cfg['app'],
 *     'app_secret' => $cfg['secret'],
 *     'default_graph_version' => $cfg['version'],
 *     'persistent_data_handler' => new SessionHandler($this->getRequest()->getSession()),
 * ]);
 *
 * @author Flavius
 * @version 1.0
 */
class SessionHandler implements PersistentDataInterface
{
    private $session = false;
    public function __construct($session) {
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     * @see \Facebook\PersistentData\PersistentDataInterface::get()
     */
    public function get($key) {
        return $this->session->consume("Facebook.{$key}");
    }

    /**
     * {@inheritDoc}
     * @see \Facebook\PersistentData\PersistentDataInterface::set()
     */
    public function set($key, $value) {
        $this->session->write("Facebook.{$key}", $value);
    }
}

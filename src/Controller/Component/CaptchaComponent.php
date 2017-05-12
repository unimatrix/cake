<?php

namespace Unimatrix\Cake\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Client;
use RuntimeException;

/**
 * Captcha component
 * Checks to see if the captcha was actually valid or not
 *
 * Usage:
 * --------------------------------------------------------------------
 * $this->loadComponent('Unimatrix/Cake.Captcha');
 *
 * // then in your controller action
 * if($this->request->is('post')) {
 *     if($this->Captcha->verify()) {
 *         ...
 *     } else $this->Flash->error('Captcha invalid')
 * }
 *
 * @author Flavius
 * @version 0.2
 */
class CaptchaComponent extends Component
{
    // the verification endpoint
    protected $_api = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Verify captcha
     * @throws RuntimeException if configuration is not found
     * @return bool
     */
    public function verify() {
        // check for config
        $secret = Configure::read('Captcha.secret');
        if(!$secret)
            throw new RuntimeException("Captcha Component: Error in configuration, secret key not found");

        // get g-recaptcha-response
        $recaptcha = $this->requestRecaptcha();
        if($recaptcha) {
            // perform the request to google
            $http = new Client();
            $response = $http->post($this->_api, [
                'secret' => $secret,
                'response' => $recaptcha,
                'remoteip' => $this->getController()->request->clientIP()
            ]);

            // got expected json response?
            if($response->json)
                if($response->json['success'])
                    return true;
        }

        // return false by default
        return false;
    }

    /**
     * Request recaptcha value
     * Also removes it from the request
     * @return boolean|string
     */
    private function requestRecaptcha() {
        $return = false;
        $request = $this->getController()->request;
        if($request->is('post')) {
            $body = $request->getParsedBody();
            if(is_array($body)) {
                $dirty = false;
                foreach($body as $name => $value) {
                    if($name === 'g-recaptcha-response') {
                        $dirty = true;
                        unset($body[$name]);
                        if($value)
                            $return = $value;
                    }
                }

                // request changed, overwrite request
                if($dirty)
                    $this->getController()->request = $request->withParsedBody($body);
            }
        }

        return $return;
    }
}

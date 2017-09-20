<?php

namespace Unimatrix\Cake\Error\Middleware;

use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Core\Configure;

/**
 * Email Error Handler Middleware
 * Send a debug email for each website exception
 *
 * Important: Make sure you remove your ErrorHandlerMiddleware from your Application
 * Note: Only works on live environments (debug = false)
 * Note2: is implemented in bootstrap (added to middleware automatically)
 *
 * @author Flavius
 * @version 1.0
 */
class EmailErrorHandlerMiddleware extends ErrorHandlerMiddleware
{
    // debug & email
    private $debug = false;
    private $email = false;

    // skip these exceptions
    protected $_skipExceptions = [
        'Cake\Network\Exception\NotFoundException',
        'Cake\Routing\Exception\MissingRouteException'
    ];

    /**
     * Constructor
     *
     * @param array $options The options for error handling.
     */
    public function __construct($options = []) {
        // set debug & email
        $this->debug = Configure::read('debug');
        if(!$this->debug)
            $this->email = new EmailComponent(new ComponentRegistry());

        // run parent
        parent::__construct($options);
    }

    /**
     * Intercept exception handling to send a mail before continuing with the default logic
     * @see \Cake\Error\Middleware\ErrorHandlerMiddleware::handleException()
     */
    public function handleException($exception, $request, $response){
        // send a debug mail with the exception
        if($this->email && !in_array(get_class($exception), $this->_skipExceptions))
            $this->email->debug('Website Exception', Misc::dump($exception, $exception->getMessage(), true));

        // continue with exception handle logic
        return parent::handleException($exception, $request, $response);
    }
}

<?php

namespace Unimatrix\Cake\Error\Middleware;

use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Core\Configure;
use Cake\Controller\ComponentRegistry;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Exception\MissingControllerException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Email Error Handler Middleware
 * Send a debug email for each website exception
 * Note: Only works on live environments (debug = false)
 *
 * Usage exmaple (in src/application.php)
 * ----------------------------------------------------------------
 * search for -> >add(ErrorHandlerMiddleware::class)
 * replace with -> add(EmailErrorHandlerMiddleware::class)
 *
 * Don't forget about
 * use Unimatrix\Cake\Error\Middleware\EmailErrorHandlerMiddleware;
 *
 * @author Flavius
 * @version 1.2
 */
class EmailErrorHandlerMiddleware extends ErrorHandlerMiddleware
{
    // debug
    private $debug = false;

    // skip these exceptions
    protected $_skipExceptions = [
        NotFoundException::class,
        MissingRouteException::class,
        MissingControllerException::class,
        RecordNotFoundException::class
    ];

    /**
     * Constructor
     * @param array $options The options for error handling.
     */
    public function __construct($options = []) {
        // set debug
        $this->debug = Configure::read('debug');

        // run parent
        parent::__construct($options);
    }

    /**
     * Intercept exception handling to send a mail before continuing with the default logic
     * @see \Cake\Error\Middleware\ErrorHandlerMiddleware::handleException()
     */
    public function handleException($exception, $request, $response){
        // send a debug mail with the exception
        if(!$this->debug && !in_array(get_class($exception), $this->_skipExceptions))
            $this->_email('Website Exception', Misc::dump($exception, $exception->getMessage(), true));

        // continue with exception handle logic
        return parent::handleException($exception, $request, $response);
    }

    /**
     * Send the email
     * @param string $title
     * @param string $body
     * @return array
     */
    protected function _email($title, $body) {
        // @codeCoverageIgnoreStart
        return (new EmailComponent(new ComponentRegistry()))->debug($title, $body);
        // @codeCoverageIgnoreEnd
    }
}

<?php

namespace Unimatrix\Cake\Error;

use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Core\Configure;
use Cake\Controller\ComponentRegistry;
use Cake\Error\ErrorHandler;
use Cake\Error\PHP7ErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Exception\MissingRouteException;
use Cake\Routing\Exception\MissingControllerException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Exception;

/**
 * Email Error Handler
 * Send a debug email for each fatal error or exception thrown
 * Note: Only works on live environments (debug = false)
 *
 * Usage exmaple (in config/bootstrap.php)
 * ----------------------------------------------------------------
 * search for -> (new ErrorHandler(Configure::read('Error')))->register();
 * replace with -> (new EmailErrorHandler(Configure::read('Error')))->register();
 *
 * Don't forget about
 * use Unimatrix\Cake\Error\EmailErrorHandler;
 *
 * @author Flavius
 * @version 1.2
 */
class EmailErrorHandler extends ErrorHandler
{
    // debug
    private $debug = false;

    // skip these errors and exceptions
    protected $_skipErrors = [E_NOTICE, E_WARNING];
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
     * Intercept error handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleError()
     */
    public function handleError($code, $description, $file = null, $line = null, $context = null) {
        // get error type
        $type = $this->mapErrorCode($code)[0] . ' Error: ';

        // send a debug mail with the error
        if(!$this->debug && !in_array($code, $this->_skipErrors))
            $this->_email('Website Error', Misc::dump([
                'type' => rtrim($type, ': '),
                'description' => $description,
                'file' => $file,
                'line' => $line,
                'code' => $code,
                'context' => $context
            ], $type . $description, true));

        // continue with error handle logic
        return parent::handleError($code, $description, $file, $line, $context);
    }

    /**
     * Intercept exception handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleException()
     */
    public function handleException(Exception $exception) {
        // untangle message from php7errorexception
        $message = $exception instanceof PHP7ErrorException ? $exception->getError()->getMessage(): $exception->getMessage();

        // send a debug mail with the exception
        if(!$this->debug && !in_array(get_class($exception), $this->_skipExceptions))
            $this->_email('Website Exception', Misc::dump($exception, $message, true));

        // continue with exception handle logic
        parent::handleException($exception);
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

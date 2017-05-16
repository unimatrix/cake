<?php

namespace Unimatrix\Cake\Console;

use Unimatrix\Cake\Lib\Misc;
use Unimatrix\Cake\Controller\Component\EmailComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Console\ConsoleErrorHandler;
use Cake\Error\PHP7ErrorException;
use Cake\Core\Configure;
use Exception;

/**
 * Email Console Error Handler (for cli)
 * Send a debug email for each fatal error or exception thrown
 * Note: Only works on live environments (debug = false)
 *
 * Usage exmaple (in bootstrap)
 * ----------------------------------------------------------------
 * search for -> (new ConsoleErrorHandler(Configure::read('Error')))->register();
 * replace with -> (new EmailConsoleErrorHandler(Configure::read('Error')))->register();
 *
 * Don't forget about
 * use Unimatrix\Cake\Console\EmailConsoleErrorHandler;
 *
 * @author Flavius
 * @version 1.0
 */
class EmailConsoleErrorHandler extends ConsoleErrorHandler
{
    // debug & email
    private $debug = false;
    private $email = false;

    // skip these errors and exceptions
    protected $_skipErrors = [E_NOTICE, E_WARNING];
    protected $_skipExceptions = [];

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
     * Intercept error handling to send a mail before continuing with the default logic
     * @see \Cake\Error\BaseErrorHandler::handleError()
     */
    public function handleError($code, $description, $file = null, $line = null, $context = null) {
        // get error type
        $type = $this->mapErrorCode($code)[0] . ' Error: ';

        // send a debug mail with the error
        if($this->email && !in_array($code, $this->_skipErrors))
            $this->email->debug('CLI Error', Misc::dump([
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
        if($this->email && !in_array(get_class($exception), $this->_skipExceptions))
            $this->email->debug('CLI Exception', Misc::dump($exception, $message, true));

        // continue with exception handle logic
        parent::handleException($exception);
    }
}

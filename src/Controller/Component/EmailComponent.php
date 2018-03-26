<?php

namespace Unimatrix\Cake\Controller\Component;

use Cake\Mailer\Email;
use Cake\Controller\Component;
use Unimatrix\Cake\Lib\Misc;

/**
 * Email component
 * Handle debug and normal mail operations
 *
 * Config example:
 * ----------------------------------------------------------------
 * 'EmailTransport' => [
 *     'default' => [
 *         'className' => 'Mail',
 *         'additionalParameters' => '-fcontact@domain.tld',
 *     ],
 * ],
 *
 * 'Email' => [
 *     'default' => [
 *         'transport' => 'default',
 *         'emailFormat' => 'html',
 *         'from' => ['contact@domain.tld' => 'Domain.tld'],
 *         'sender' => ['contact@domain.tld' => 'Domain.tld'],
 *         'to' => ['name1@company.tld', 'name2@company.tld'],
 *         'bcc' => 'monitor@unimatrix.ro',
 *         'headers' => ['X-Mailer' => 'Domain.tld'],
 *         'charset' => 'utf-8',
 *         'headerCharset' => 'utf-8',
 *     ],
 *     'debug' => [
 *         'transport' => 'default',
 *         'emailFormat' => 'html',
 *         'from' => ['contact@domain.tld' => 'Domain.tld'],
 *         'to' => 'monitor@unimatrix.ro',
 *         'log' => true,
 *         'charset' => 'utf-8',
 *         'headerCharset' => 'utf-8',
 *     ],
 * ],
 *
 * Usage example:
 * ----------------------------------------------------------------
 * $this->loadComponent('Unimatrix/Cake.Email');
 *
 * // send email
 * $this->Email->send([
 *     'subject' => 'Solicitare contact',
 *     'form' => $this->request->getData()
 * ]);
 *
 * // send email debug
 * $this->Email->debug('New subscriber', $this->request->data['email'], true, false);
 *
 * @author Flavius
 * @version 1.1
 */
class EmailComponent extends Component
{
    /**
     * Send emails
     *
     * @param array $data
     * @param string $template
     * @param string $layout
     * @param string $config
     * @throws \Cake\Network\Exception\SocketException if mail could not be sent
     */
    public function send($data = [], $template = 'default', $layout = 'default', $config = 'default') {
        // initialize email
        $email = (new Email($config))
            ->setTemplate($template)
            ->setLayout($layout);

        // to?
        if(isset($data['to']))
            $email->setTo($data['to']);

        // get brand
        $from = $email->getFrom();
        $brand = reset($from);

        // subject?
        $subject = $data['subject'] ?? $email->getSubject();
        $email->setSubject(trim($config == 'debug' ? $brand . ' report: ' . $subject : $subject . ' - ' . $brand));

        // set template variables
        $email->setViewVars([
            'subject' => $subject,
            'brand' => $brand,
            'form' => $data['form'] ?? [],
            'info' => [
                'ip' => $this->getController()->request->clientIP(),
                'useragent' => env('HTTP_USER_AGENT'),
                'date' => strftime('%d.%m.%Y %H:%M')
            ]
        ]);

        // send email
        $email->send();
    }

    /**
     * Send debug emails
     *
     * @param string $subject
     * @param string $body
     * @param bool $request
     * @param bool $server
     */
    public function debug($subject = null, $body = null, $request = true, $server = true) {
        // initialize email
        $email = (new Email('debug'))
            ->setTemplate('Unimatrix/Cake.debug')
            ->setLayout('Unimatrix/Cake.debug');

        // get controller and method
        $location = [];
        foreach(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $one) {
            // ignore current function
            if($one['function'] == __FUNCTION__)
                continue;

            // check if private or protected
            $check = new \ReflectionMethod($one['class'], $one['function']);
            if($check->isPrivate() || $check->isProtected()) {
                $location[3] = $one['function'];
                continue;
            }

            // set class and function
            $location[1] = str_replace(['Controller', 'App\\\\'], null, $one['class']);
            $location[2] = $one['function'];
            break;
        }

        // sort before using
        ksort($location);

        // overwrite locations
        if(isset($location[1])) {
            if($location[1] == 'Unimatrix\Cake\Console\EmailConsoleErrorHandler') $location = ['EmailConsoleErrorHandler'];
            elseif($location[1] == 'Unimatrix\Cake\Error\EmailErrorHandler') $location = ['EmailErrorHandler'];
            elseif($location[1] == 'Unimatrix\Cake\Error\Middleware\EmailErrorHandlerMiddleware') $location = ['EmailErrorHandlerMiddleware'];
        }

        // get brand
        $from = $email->getFrom();
        $brand = reset($from);

        // set subject
        $email->setSubject($brand . ' report: [' . implode('->', $location) . '] ' . $subject);

        // body start
        $body = $body == strip_tags($body) ? nl2br($body) : $body;

        // show request
        if($request) {
            if(isset($_POST) && !empty($_POST))
                $body .= Misc::dump($_POST, '$_POST', true);

            if(isset($_GET) && !empty($_GET))
                $body .= Misc::dump($_GET, '$_GET', true);

            if(isset($_COOKIE) && !empty($_COOKIE))
                $body .= Misc::dump($_COOKIE, '$_COOKIE', true);

            if(isset($_SESSION) && !empty($_SESSION))
                $body .= Misc::dump($_SESSION, '$_SESSION', true);
        }

        // show server
        if($server)
            $body .= Misc::dump($_SERVER, '$_SERVER', true);

        // send email
        $email->send($body);
    }
}

<?php

namespace Unimatrix\Cake\Test\TestCase\Controller\Component;

use Cake\TestSuite\TestCase;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\Mailer\Email;
use Cake\Core\Plugin;
use Cake\Log\Log;
use Unimatrix\Cake\Lib\Misc;

class EmailComponentTest extends TestCase
{
    protected $email;

    public function setUp() {
        parent::setUp();
        $request = new ServerRequest();
        $controller = new Controller($request);
        $controller->loadComponent('Unimatrix/Cake.Email');
        $this->email = $controller->Email;

        Email::setConfig('default', [
            'transport' => 'default',
            'emailFormat' => 'html',
            'from' => ['contact@brand.tld' => 'Brand.tld'],
            'sender' => ['contact@brand.tld' => 'Brand.tld'],
            'to' => ['name1@brand.tld', 'name2@brand.tld'],
            'headers' => ['X-Mailer' => 'Brand.tld'],
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ]);
        Email::setConfig('debug', [
            'transport' => 'default',
            'emailFormat' => 'html',
            'from' => ['contact@brand.tld' => 'Brand.tld'],
            'to' => 'monitor@brand.tld',
            'log' => true,
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ]);
        Email::setConfigTransport('default', [
            'className' => 'Debug'
        ]);
    }

    public function tearDown() {
        parent::tearDown();
        Plugin::unload();
        Log::drop('email');
        Log::drop('debug');
        Email::drop('default');
        Email::drop('debug');
        Email::dropTransport('default');
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        unset($_SESSION);
    }

    public function testSend() {
        $result = $this->email->send([
            'to' => 'office@brand.tld',
            'subject' => 'Test Subject'
        ], '');

        $this->assertContains('From: "Brand.tld" <contact@brand.tld>', $result['headers']);
        $this->assertContains('To: office@brand.tld', $result['headers']);
        $this->assertContains('X-Mailer: Brand.tld', $result['headers']);
        $this->assertContains('Subject: Test Subject - Brand.tld', $result['headers']);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $result['headers']);
    }

    public function testDebug() {
        Plugin::load('Unimatrix/Cake', ['path' => PLUGIN_PATH . DS]);

        $_POST = ['test' => 'post'];
        $_GET = ['test' => 'get'];
        $_COOKIE = ['test' => 'cookie'];
        $_SESSION = ['test' => 'session'];
        $result = $this->email->debug('New debug email', 'A debug email has been generated', true, true);

        $this->assertContains('From: "Brand.tld" <contact@brand.tld>', $result['headers']);
        $this->assertContains('To: monitor@brand.tld', $result['headers']);
        $this->assertContains('Subject: Brand.tld report: [EmailComponentTest] New debug email', $result['headers']);
        $this->assertContains('Content-Type: text/html; charset=UTF-8', $result['headers']);

        $content = Misc::dump($_POST, '$_POST', true);
        $content .= Misc::dump($_GET, '$_GET', true);
        $content .= Misc::dump($_COOKIE, '$_COOKIE', true);
        $content .= Misc::dump($_SESSION, '$_SESSION', true);
        $content .= Misc::dump($_SERVER, '$_SERVER', true);
        $body = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>Brand.tld report: [EmailComponentTest] New debug email</title>
</head>
<body>
    A debug email has been generated{$content}
</body>
</html>

EOT;
        $this->assertSame($result['message'], str_replace(["\n", "\r\r\n"], "\r\n", $body));
    }
}

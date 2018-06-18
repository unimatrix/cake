<?php

namespace Unimatrix\Cake\Test\TestCase\Controller\Component;

use Cake\TestSuite\TestCase;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use DateTimeZone;
use DateTime;

class CookieComponentTest extends TestCase
{
    protected $realCtrl;
    protected $cacheCtrl;

    public function setUp() {
        parent::setUp();
        $request = new ServerRequest(['cookies' => [
            'set_cookie' => 'set data',
            'set_cookie_array' => [
                'start' => 123,
                'end' => 456
            ]
        ]]);
        $this->realCtrl = new Controller($request);
        $this->realCtrl->loadComponent('Unimatrix/Cake.Cookie');

        $request = new ServerRequest();
        $this->cacheCtrl = new Controller($request);
        $this->cacheCtrl->loadComponent('Unimatrix/Cake.Cookie');
    }

    public function testDefaultConfig() {
        $name = 'test_cookie';
        $data = 'sample data';
        $this->cacheCtrl->Cookie->write($name, $data);
        $result = $this->cacheCtrl->getResponse()->getCookie($name);

        $this->assertSame($name, $result['name']);
        $this->assertSame($data, $result['value']);
        $this->assertSame('/', $result['path']);
        $this->assertSame('', $result['domain']);
        $this->assertFalse($result['secure']);
        $this->assertFalse($result['httpOnly']);
        $this->assertNull($result['expire']);
    }

    public function testCheck() {
        $name1 = 'set_cookie';
        $name2 = 'set_cookie_array';

        $this->assertFalse($this->realCtrl->Cookie->check('non_existent_cookie'));
        $this->assertTrue($this->realCtrl->Cookie->check($name1));
        $this->assertTrue($this->realCtrl->Cookie->check($name2));
    }

    public function testCheckFromCache() {
        $name1 = 'test_cookie';
        $name2 = 'test_cookie_array';
        $data1 = 'cache data';
        $data2 = ['foo' => 'a', 'bar' => 'b'];
        $this->assertFalse($this->cacheCtrl->Cookie->check($name1));
        $this->assertFalse($this->cacheCtrl->Cookie->check($name2));

        $this->cacheCtrl->Cookie->write($name1, $data1);
        $this->cacheCtrl->Cookie->write($name2, $data2);

        $this->assertTrue($this->cacheCtrl->Cookie->check($name1));
        $this->assertTrue($this->cacheCtrl->Cookie->check($name2));
    }

    public function testRead() {
        $name1 = 'set_cookie';
        $name2 = 'set_cookie_array';

        $this->assertNull($this->realCtrl->Cookie->read('non_existent_cookie'));
        $this->assertSame('set data', $this->realCtrl->Cookie->read($name1));
        $this->assertSame(123, $this->realCtrl->Cookie->read($name2, 'start'));
        $this->assertSame(456, $this->realCtrl->Cookie->read($name2, 'end'));
    }

    public function testReadFromCache() {
        $name1 = 'test_cookie';
        $name2 = 'test_cookie_array';
        $data1 = 'cache data';
        $data2 = ['foo' => 'a', 'bar' => 'b'];
        $this->assertNull($this->cacheCtrl->Cookie->read($name1));
        $this->assertNull($this->cacheCtrl->Cookie->read($name2));

        $this->cacheCtrl->Cookie->write($name1, $data1);
        $this->cacheCtrl->Cookie->write($name2, $data2);

        $this->assertSame($data1, $this->cacheCtrl->Cookie->read($name1));
        $this->assertSame('a', $this->cacheCtrl->Cookie->read($name2, 'foo'));
        $this->assertSame('b', $this->cacheCtrl->Cookie->read($name2, 'bar'));
    }

    public function testWrite() {
        $name1 = 'test_cookie';
        $name2 = 'test_cookie_array';
        $data1 = 'cache data';
        $data2 = ['foo' => 'a', 'bar' => 'b'];
        $this->assertNull($this->cacheCtrl->getResponse()->getCookie($name1));
        $this->assertNull($this->cacheCtrl->getResponse()->getCookie($name2));

        $this->cacheCtrl->Cookie->write($name1, $data1);
        $this->cacheCtrl->Cookie->write($name2, $data2);

        $this->assertSame($data1, $this->cacheCtrl->getResponse()->getCookie($name1)['value']);
        $this->assertSame('a', json_decode($this->cacheCtrl->getResponse()->getCookie($name2)['value'])->foo);
        $this->assertSame('b', json_decode($this->cacheCtrl->getResponse()->getCookie($name2)['value'])->bar);
    }

    public function testWriteWithExpiry() {
        $name = 'test_cookie';
        $data = 'cache data';
        $options = ['expire' => '+1 year'];

        $this->cacheCtrl->Cookie->write($name, $data, $options);
        $this->assertSame(
            (string)(new DateTime($options['expire']))->setTimezone(new DateTimeZone('GMT'))->getTimestamp(),
            $this->cacheCtrl->getResponse()->getCookie($name)['expire']
        );
    }

    public function testDelete() {
        $name1 = 'set_cookie';
        $name2 = 'set_cookie_array';
        $this->assertNotNull($this->realCtrl->Cookie->read($name1));
        $this->assertNotNull($this->realCtrl->Cookie->read($name2));

        $this->realCtrl->Cookie->delete($name1);
        $this->realCtrl->Cookie->delete($name2);

        $expected = (string)(DateTime::createFromFormat('U', 1))->getTimestamp();
        $this->assertSame($expected, $this->realCtrl->getResponse()->getCookie($name1)['expire']);
        $this->assertSame($expected, $this->realCtrl->getResponse()->getCookie($name2)['expire']);
    }

    public function testDeleteFromCache() {
        $name1 = 'test_cookie';
        $name2 = 'test_cookie_array';
        $data1 = 'cache data';
        $data2 = ['foo' => 'a', 'bar' => 'b'];
        $this->cacheCtrl->Cookie->write($name1, $data1);
        $this->cacheCtrl->Cookie->write($name2, $data2);
        $this->cacheCtrl->Cookie->delete($name1);
        $this->cacheCtrl->Cookie->delete($name2);

        $this->assertNull($this->cacheCtrl->Cookie->read($name1));
        $this->assertNull($this->cacheCtrl->Cookie->read($name2));
    }
}

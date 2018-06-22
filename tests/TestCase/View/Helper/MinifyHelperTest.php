<?php

namespace Unimatrix\Cake\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Cake\Core\Plugin;
use Cake\Routing\Router;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Core\Exception\Exception;
use Unimatrix\Cake\View\Helper\MinifyHelper;

class MinifyHelperTest extends TestCase
{
    protected $view;
    protected $folders = [];

	public function setUp() {
		parent::setUp();
		$this->view = new View(null);

        $this->folders['css'] = new Folder(WWW_ROOT . 'css', true);
        $this->folders['js'] = new Folder(WWW_ROOT . 'js', true);
        $this->folders['cache-css'] = new Folder(WWW_ROOT . 'cache-css', true);
        $this->folders['cache-js'] = new Folder(WWW_ROOT . 'cache-js', true);

        $css = new File(WWW_ROOT . 'css' . DS . 'test-file.css', true);
        $css->write("body {\n\tbackground: url('../test.jpg');\tcolor: #333;\n}\n");
        $css->close();
        $js = new File(WWW_ROOT . 'js' . DS . 'test-file.js', true);
        $js->write("var Object = function() {\n\tconsole.log('hello');\n};\n");
        $js->close();
	}

	public function tearDown() {
	    parent::tearDown();
	    Plugin::unload();
	    foreach($this->folders as $folder)
	        $folder->delete();
	}

    public function testAfterLayout() {
        $sample = "<html>\n<head>\n</head><body>\n<div class='main'>Hello World!</div></body></html>";

        $helper = new MinifyHelper($this->view);
        $this->view->Blocks->set('content', $sample);
        $helper->afterLayout();
        $this->assertSame('<html><head><body><div class=main>Hello World!</div>', $this->view->Blocks->get('content'));

        $helper = new MinifyHelper($this->view, ['config' => ['html' => ['doRemoveOmittedHtmlTags' => false]]]);
        $this->view->Blocks->set('content', $sample);
        $helper->afterLayout();
        $this->assertSame('<html><head></head><body><div class=main>Hello World!</div></body></html>', $this->view->Blocks->get('content'));

        $helper = new MinifyHelper($this->view, ['compress' => ['html' => false]]);
        $this->view->Blocks->set('content', $sample);
        $helper->afterLayout();
        $this->assertSame($sample, $this->view->Blocks->get('content'));
    }

    public function testFetchException() {
        $helper = new MinifyHelper($this->view);
        $helper->style('Unimatrix/Cake.error500');
        $this->expectException(Exception::class);
        $helper->fetch('flash');
    }

    public function testAssetLoading() {
        $helper = new MinifyHelper($this->view);
        $helper->style('Unimatrix/Cake.error500');
        ob_start();
        $helper->fetch('style');
        $result = ob_get_contents();
        $this->assertSame($helper->Html->css([]), $result);
        ob_end_clean();

        Plugin::load('Unimatrix/Cake', ['path' => PLUGIN_PATH . DS]);
        $helper = new MinifyHelper($this->view);
        $helper->style(['test-file.css', 'Unimatrix/Cake.error500.css']);
        ob_start();
        $helper->fetch('style');
        $result = ob_get_contents();
        $this->assertSame($helper->Html->css(['test-file', 'Unimatrix/Cake.error500']), $result);
        ob_end_clean();
    }

    public function testOrganize() {
        $helper = new MinifyHelper($this->view);
        $helper->style();
        ob_start();
        $helper->fetch('style');
        $result = ob_get_contents();
        $this->assertSame($helper->Html->css([]), $result);
        ob_end_clean();

        $helper = new MinifyHelper($this->view);
        $helper->script(1);
        ob_start();
        $helper->fetch('script');
        $result = ob_get_contents();
        $this->assertSame($helper->Html->script([]), $result);
        ob_end_clean();

        $helper = new MinifyHelper($this->view);
        $helper->style('test-file');
        $helper->style('test-file');
        ob_start();
        $helper->fetch('style');
        $result = ob_get_contents();
        $this->assertSame($helper->Html->css(['test-file.css']), $result);
        ob_end_clean();
    }

    public function testOrganizeException() {
        $helper = new MinifyHelper($this->view);
        $this->expectException(Exception::class);
        $helper->style('unknown', 'flash');
    }

    public function testCssAndJsMinification() {
        $helper = new MinifyHelper($this->view);
        $helper->style('test-file.css');
        ob_start();
        $helper->fetch('style', true);
        $expected = [
            'link' => ['rel' => 'stylesheet', 'href' => 'preg:/\/cache\-css\/cache\-([0-9]{10})\-([a-f0-9]{32})\.css/']
        ];
        $result = ob_get_contents();
        $this->assertHtml($expected, $result);
        ob_end_clean();

        $generated = str_replace(['<link rel="stylesheet" href="', '"/>'], null, $result);
        $file = new File(WWW_ROOT . trim(str_replace('/', DS, $generated), DS));
        $this->assertSame('body{background:url(../test.jpg);color:#333}', $file->read());

        $helper = new MinifyHelper($this->view);
        $helper->script('test-file.js');
        ob_start();
        $helper->fetch('script', true);
        $expected = [
            'script' => ['src' => 'preg:/\/cache\-js\/cache\-([0-9]{10})\-([a-f0-9]{32})\.js/'],
            '/script'
        ];
        $result = ob_get_contents();
        $this->assertHtml($expected, $result);
        ob_end_clean();

        $generated = str_replace(['<script src="', '"></script>'], null, $result);
        $file = new File(WWW_ROOT . trim(str_replace('/', DS, $generated), DS));
        $this->assertSame('var Object=function(){console.log(\'hello\')};', $file->read());

        Plugin::load('Unimatrix/Cake', ['path' => PLUGIN_PATH . DS]);
        $helper = new MinifyHelper($this->view);
        $helper->style('Unimatrix/Cake.error500');
        ob_start();
        $helper->fetch('style', true);
        $expected = [
            'link' => ['rel' => 'stylesheet', 'href' => 'preg:/\/cache\-css\/cache\-([0-9]{10})\-([a-f0-9]{32})\.css/']
        ];
        $result = ob_get_contents();
        $this->assertHtml($expected, $result);
        ob_end_clean();
    }

    public function testNoFilesToMinify() {
        $helper = new MinifyHelper($this->view);
        $helper->style();
        ob_start();
        $helper->fetch('style', true);
        $this->assertEmpty(ob_get_clean());

        $helper = new MinifyHelper($this->view);
        $helper->script();
        ob_start();
        $helper->fetch('script', true);
        $this->assertEmpty(ob_get_clean());
    }

    public function testInlineException() {
        $helper = new MinifyHelper($this->view);
        $this->expectException(Exception::class);
        $helper->inline('flash');
    }

    public function testInlineCssAndJs() {
        $helper = new MinifyHelper($this->view);
        ob_start();
        $helper->inline('script', 'var SuperGlobal = true;');
        $this->assertSame('<script>var SuperGlobal=!0</script>', ob_get_clean());
        $this->assertFalse($helper->inline('script', 'var SuperGlobal = true;', true));

        $helper = new MinifyHelper($this->view);
        $result = $helper->inline('style', 'table { display: block; }', true);
        $this->assertSame('<style>table{display:block}</style>', $result);
        $this->assertFalse($helper->inline('style', 'table { display: block; }', true));
    }

    public function testNoDataToInline() {
        $helper = new MinifyHelper($this->view);
        $this->assertEmpty($helper->inline('script', null, true));

        $helper = new MinifyHelper($this->view);
        $this->assertEmpty($helper->inline('style', null, true));
    }

    public function testInlineMorphing() {
        Router::connect('/');
        $helper = new MinifyHelper($this->view);
        $result = $helper->inline('style', 'div { background: url("../test.jpg#"); } @import "../css.css"', true);
        $this->assertSame('<style>@import "/../css.css";div{background:url("/../test.jpg#")}</style>', $result);
        Router::reload();
    }
}

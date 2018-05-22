<?php

namespace Unimatrix\Cake\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\View\Helper;
use Cake\View\View;
use Cake\Utility\Inflector;
use Cake\Core\Exception\Exception;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\PathConverter\Converter;
use voku\helper\HtmlMin as HTML;

class MinifyHelperTest extends TestCase
{
    public function testSomething() {
        $this->assertTrue(true);
    }
}

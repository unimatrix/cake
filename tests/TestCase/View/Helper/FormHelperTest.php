<?php

namespace Unimatrix\Cake\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Cake\View\Widget\TextareaWidget;
use Cake\View\Form\ContextInterface;
use Cake\View\Helper\FormHelper as CakeFormHelper;
use Unimatrix\Cake\View\Helper\FormHelper;

class TestWidget extends TextareaWidget
{
    public function render(array $data, ContextInterface $context) {
        return isset($data['view']) ? 'Y' : 'N';
    }
}

class FormHelperTest extends TestCase
{
    public function testPassedView() {
		$view = new View(null);
		$unix = new FormHelper($view, ['widgets' => ['test' => [TestWidget::class]]]);
		$cake = new CakeFormHelper($view, ['widgets' => ['test' => [TestWidget::class]]]);

        $expected = [
            ['div' => ['class' => 'input test']],
                'label' => ['for' => 'test'],
                    'Test',
                '/label',
                'Y',
            '/div'
        ];
        $this->assertHtml($expected, $unix->control('test', ['type' => 'test']));

        $expected = [
            ['div' => ['class' => 'input test']],
                'label' => ['for' => 'test'],
                    'Test',
                '/label',
                'N',
            '/div'
        ];
        $this->assertHtml($expected, $cake->control('test', ['type' => 'test']));
    }
}

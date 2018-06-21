<?php

namespace Unimatrix\Cake\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Model\Behavior\NullableBehavior;

class NullableBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.unimatrix\cake.articles',
    ];

    public function testNull() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(NullableBehavior::class);

        $article = $table->find()->first();
        $this->assertEquals(1, $article->get('id'));

        $article->set('photo', null);
        $table->save($article);
        $this->assertNull($article->get('photo'));

        $article->set('photo', '');
        $table->save($article);
        $this->assertNull($article->get('photo'));
    }
}

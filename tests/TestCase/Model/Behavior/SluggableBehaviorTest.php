<?php

namespace Unimatrix\Cake\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Model\Behavior\SluggableBehavior;

class SluggableBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.unimatrix\cake.articles',
    ];

    public function testNewEntity() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(SluggableBehavior::class);
        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article'
        ]);
        $table->save($article);
        $this->assertSame('test-new-article', $article->get('slug'));
    }

    public function testNewEntityWithDuplicateTitle() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(SluggableBehavior::class);
        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Second Article',
            'body' => 'The body of the new article'
        ]);
        $table->save($article);
        $this->assertSame('second-article-1', $article->get('slug'));
    }

    public function testExistingEntity() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(SluggableBehavior::class);
        $article = $table->find()->first();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article'
        ]);
        $table->save($article);
        $this->assertSame('first-article', $article->get('slug'));
    }

    public function testOppositeConfig() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(SluggableBehavior::class, [
            'field' => 'body',
            'replacement' => '|',
            'overwrite' => true,
            'unique' => false
        ]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article'
        ]);
        $table->save($article);
        $this->assertSame('the|body|of|the|new|article', $article->get('slug'));

        $article->set('body', 'New, |ăsș-  1!@# Body');
        $table->save($article);
        $this->assertSame('new|ass|1|body', $article->get('slug'));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'New, |ăsș-1!@# Body'
        ]);
        $table->save($article);
        $this->assertSame('new|ass|1|body', $article->get('slug'));
    }

    public function testFinder() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(SluggableBehavior::class);
        $article = $table->find('slug', ['second-article'])->first();
        $this->assertEquals(2, $article->get('id'));
    }
}

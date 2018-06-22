<?php

namespace Unimatrix\Cake\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Unimatrix\Cake\Model\Behavior\UploadableBehavior;
use RuntimeException;

class UploadableBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.unimatrix\cake.articles',
    ];

    public function tearDown() {
        parent::tearDown();
        $uploads = new Folder(WWW_ROOT . 'img', true);
        $uploads->delete();
    }

    public function testNoConfig() {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration missing for UploadableBehavior');

        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class);
    }

    public function testWithoutFile() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:uuid']]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article'
        ]);
        $table->save($article);
        $this->assertNull($article->get('photo'));

        $deleted = $table->delete($article);
        $this->assertTrue($deleted);
    }

    public function testWithFileErrored() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:uuid']]);

        $data = [
            'title' => 'Test New Article',
            'body' => 'The body of the new article',
            'photo' => [
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => __FILE__,
                'error' => 1,
                'size' => 123
            ]
        ];

        $article = $table->newEntity();
        $article = $table->patchEntity($article, $data);
        $this->assertSame('isUnderPhpSizeLimit', key($article->getError('photo')));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, ['photo' => ['error' => 2] + $data['photo']] + $data);
        $this->assertSame('isUnderFormSizeLimit', key($article->getError('photo')));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, ['photo' => ['error' => 3] + $data['photo']] + $data);
        $this->assertSame('isCompletedUpload', key($article->getError('photo')));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, ['photo' => ['error' => 6] + $data['photo']] + $data);
        $this->assertSame('isTemporaryDirectory', key($article->getError('photo')));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, ['photo' => ['error' => 7] + $data['photo']] + $data);
        $this->assertSame('isSuccessfulWrite', key($article->getError('photo')));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, ['photo' => ['error' => 8] + $data['photo']] + $data);
        $this->assertSame('isNotStoppedByExtension', key($article->getError('photo')));

        $article = $table->newEntity();
        $article = $table->patchEntity($article, ['photo' => ['error' => 1337] + $data['photo']] + $data);
        $table->save($article);
        $this->assertEmpty($article->get('photo'));
    }

    public function testWithFileAsUuid() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:uuid']]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article',
            'photo' => [
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => __FILE__,
                'error' => 0,
                'size' => 123
            ]
        ]);
        $table->save($article);

        $expected = '/\/img\/articles\/([a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12})\.txt/';
        $this->assertRegExp($expected, $article->get('photo'));
    }

    public function testWithFileAsMd5() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:md5']]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article',
            'photo' => [
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => __FILE__,
                'error' => 0,
                'size' => 123
            ]
        ]);
        $table->save($article);

        $expected = '/\/img\/articles\/([a-f0-9]{32})\.txt/';
        $this->assertRegExp($expected, $article->get('photo'));
    }

    public function testFileDeletionOnOverwrite() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:uuid']]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article',
            'photo' => [
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => __FILE__,
                'error' => 0,
                'size' => 123
            ]
        ]);
        $table->save($article);
        $file = new File(WWW_ROOT . trim(str_replace('/', DS, $article->get('photo')), DS));
        $this->assertTrue($file->exists());

        $article->set('photo', [
            'name' => 'foo.txt',
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => 0,
            'size' => 123
        ]);
        $table->save($article);
        $this->assertFalse($file->exists());
    }

    public function testFileDeletionOnEntityDelete() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:uuid']]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article',
            'photo' => [
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => __FILE__,
                'error' => 0,
                'size' => 123
            ]
        ]);
        $table->save($article);
        $file = new File(WWW_ROOT . trim(str_replace('/', DS, $article->get('photo')), DS));
        $this->assertTrue($file->exists());

        $table->delete($article);
        $this->assertFalse($file->exists());
    }

    public function testFileDeletionOnEntityDeleteAbort() {
        $table = $this->getTableLocator()->get('Articles');
        $table->addBehavior(UploadableBehavior::class, ['root' => WWW_ROOT, 'fields' => ['photo' => 'img/:model/:uuid']]);

        $article = $table->newEntity();
        $article = $table->patchEntity($article, [
            'title' => 'Test New Article',
            'body' => 'The body of the new article',
            'photo' => [
                'name' => 'foo.txt',
                'type' => 'text/plain',
                'tmp_name' => __FILE__,
                'error' => 0,
                'size' => 123
            ]
        ]);
        $table->save($article);
        $file = new File(WWW_ROOT . trim(str_replace('/', DS, $article->get('photo')), DS));
        $this->assertTrue($file->exists());
        if($file->exists())
            $file->delete();

        $table->delete($article);
    }
}

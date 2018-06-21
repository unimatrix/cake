<?php

namespace Unimatrix\Cake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'slug' => ['type' => 'string', 'null' => true],
        'author_id' => ['type' => 'integer', 'null' => true],
        'title' => ['type' => 'string', 'null' => true],
        'photo' => ['type' => 'string', 'null' => true],
        'body' => 'text',
        'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    public $records = [
        ['author_id' => 1, 'slug' => 'first-article', 'title' => 'First Article', 'body' => 'First Article Body', 'published' => 'Y', 'photo' => '/img/first-photo.jpg'],
        ['author_id' => 3, 'slug' => 'second-article', 'title' => 'Second Article', 'body' => 'Second Article Body', 'published' => 'Y', 'photo' => '/img/second-photo.jpg'],
        ['author_id' => 1, 'slug' => 'third-article', 'title' => 'Third Article', 'body' => 'Third Article Body', 'published' => 'Y', 'photo' => '/img/third-photo.jpg']
    ];
}

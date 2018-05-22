<?php

namespace Unimatrix\Cake\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Cake\Database\Type;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Utility\Text;
use Unimatrix\Cake\Validation\UploadValidation;
use ArrayObject;

class UploadableBehaviorTest extends TestCase
{
    public function testSomething() {
        $this->assertTrue(true);
    }
}

<?php

namespace Unimatrix\Cake\Test\TestCase\Database\Type;

use Cake\TestSuite\TestCase;
use Cake\Database\Driver;
use Cake\Database\Type;
use Unimatrix\Cake\Database\Type\FileType;

class FileTypeTest extends TestCase
{
    protected $type;
    protected $driver;

    public function setUp() {
        parent::setUp();
        Type::map('unimatrix.file', FileType::class);
        $this->type = Type::build('unimatrix.file');
        $this->driver = $this->getMockBuilder(Driver::class)->getMock();
    }

    public function testMarshal() {
        $this->assertNull($this->type->marshal(null));
        $this->assertEmpty($this->type->marshal(true));
        $this->assertEmpty($this->type->marshal(false));
        $this->assertEmpty($this->type->marshal(100));
        $this->assertEmpty($this->type->marshal('abc'));
        $this->assertSame(['foo', 'bar'], $this->type->marshal(['foo', 'bar']));
    }
}

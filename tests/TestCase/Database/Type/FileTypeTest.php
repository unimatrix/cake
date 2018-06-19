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
        $this->assertTrue($this->type->marshal(true));
        $this->assertFalse($this->type->marshal(false));
        $this->assertSame(100, $this->type->marshal(100));
        $this->assertSame('abc', $this->type->marshal('abc'));
        $this->assertSame(['foo', 'bar'], $this->type->marshal(['foo', 'bar']));
    }

    public function testToPHP() {
        $this->assertNull($this->type->toPHP(null, $this->driver));
        $this->assertTrue($this->type->toPHP(true, $this->driver));
        $this->assertFalse($this->type->toPHP(false, $this->driver));
        $this->assertSame(100, $this->type->toPHP(100, $this->driver));
        $this->assertSame('abc', $this->type->toPHP('abc', $this->driver));
        $this->assertSame(['foo', 'bar'], $this->type->toPHP(['foo', 'bar'], $this->driver));
    }

    public function testToDatabase() {
        $this->assertNull($this->type->toDatabase(null, $this->driver));
        $this->assertTrue($this->type->toDatabase(true, $this->driver));
        $this->assertFalse($this->type->toDatabase(false, $this->driver));
        $this->assertSame(100, $this->type->toDatabase(100, $this->driver));
        $this->assertSame('abc', $this->type->toDatabase('abc', $this->driver));
        $this->assertSame(['foo', 'bar'], $this->type->toDatabase(['foo', 'bar'], $this->driver));
    }
}

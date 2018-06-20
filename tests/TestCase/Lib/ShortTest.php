<?php

namespace Unimatrix\Cake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Unimatrix\Cake\Lib\Short;

class ShortTest extends TestCase
{
    public function testEncodeDecode() {
        $this->assertSame(Short::decode(Short::encode(0)), 0);
        $this->assertSame(Short::decode(Short::encode(50)), 50);
        $this->assertSame(Short::decode(Short::encode(100)), 100);
        $this->assertSame(Short::decode(Short::encode(5000)), 5000);
        $this->assertSame(Short::decode(Short::encode(10000)), 10000);
        $this->assertSame(Short::decode(Short::encode(500000)), 500000);
        $this->assertSame(Short::decode(Short::encode(1000000)), 1000000);
        $this->assertSame(Short::decode(Short::encode(50000000)), 50000000);
        $this->assertSame(Short::decode(Short::encode(100000000)), 100000000);
        $this->assertSame(Short::decode(Short::encode(5000000000)), 5000000000, 'test failed because of 32 bit?');
        $this->assertSame(Short::decode(Short::encode(10000000000)), 10000000000, 'test failed because of 32 bit?');
        $this->assertSame(Short::decode(Short::encode(500000000000)), 500000000000, 'test failed because of 32 bit?');
    }
}

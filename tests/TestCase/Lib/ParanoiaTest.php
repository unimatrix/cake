<?php

namespace Unimatrix\Cake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Cake\Utility\Security;
use Unimatrix\Cake\Lib\Paranoia;

class ParanoiaTest extends TestCase
{
    public function setUp() {
        parent::setUp();
        Security::setSalt('523a74hdbvju47skllf9893jsmdkv0w9386ugjvndi838765734hjsjkci85842c');
    }

    public function testEncryptDecrypt() {
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt('string to be encrypted')), 'string to be encrypted');
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(85456)), '85456');
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(true)), '1');
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(false)), false);
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(null)), false);
    }

    public function testEncryptDecryptWithCustomSecret() {
        $secret = 'my secret key';
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt('string to be encrypted', $secret), $secret), 'string to be encrypted');
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(85456, $secret), $secret), '85456');
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(true, $secret), $secret), '1');
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(false, $secret), $secret), false);
        $this->assertSame(Paranoia::decrypt(Paranoia::encrypt(null, $secret), $secret), false);
    }

    public function testEncodeDecode() {
        $this->assertSame(Paranoia::decode(Paranoia::encode('string to be encoded')), 'string to be encoded');
        $this->assertSame(Paranoia::decode(Paranoia::encode(85456)), '85456');
        $this->assertSame(Paranoia::decode(Paranoia::encode(true)), '1');
        $this->assertSame(Paranoia::decode(Paranoia::encode(false)), false);
        $this->assertSame(Paranoia::decode(Paranoia::encode(null)), false);
    }

    public function testEncodeDecodeWithCustomSecret() {
        $secret = 'my secret key';
        $this->assertSame(Paranoia::decode(Paranoia::encode('string to be encoded', $secret), $secret), 'string to be encoded');
        $this->assertSame(Paranoia::decode(Paranoia::encode(85456, $secret), $secret), '85456');
        $this->assertSame(Paranoia::decode(Paranoia::encode(true, $secret), $secret), '1');
        $this->assertSame(Paranoia::decode(Paranoia::encode(false, $secret), $secret), false);
        $this->assertSame(Paranoia::decode(Paranoia::encode(null, $secret), $secret), false);
    }

    public function testInvalidValues() {
        $this->assertFalse(Paranoia::decrypt('8'));
        $this->assertFalse(Paranoia::decode('8'));
    }

    public function testBase64EncodeIsUrlSafe() {
        $safe = '/^[a-zA-Z0-9\_\-\.]*$/';
        $this->assertRegExp($safe, Paranoia::base64encode('some input to be encoded'));
        $this->assertRegExp($safe, Paranoia::base64encode('please also encode me'));
        $this->assertRegExp($safe, Paranoia::base64encode('needs to be encoded'));
        $this->assertRegExp($safe, Paranoia::base64encode('hello? encode!'));
        $this->assertRegExp($safe, Paranoia::base64encode(true));
        $this->assertRegExp($safe, Paranoia::base64encode(99));
        $this->assertEmpty(Paranoia::base64encode(false));
        $this->assertEmpty(Paranoia::base64encode(null));
    }
}

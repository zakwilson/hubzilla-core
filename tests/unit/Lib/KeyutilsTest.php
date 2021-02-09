<?php
/*
 * Copyright (c) 2016-2017 Hubzilla
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Zotlabs\Tests\Unit\Lib;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use Zotlabs\Tests\Unit\UnitTestCase;
use Zotlabs\Lib\Keyutils;

/**
 * @brief Unit Test case for Keyutils class.
 *
 * @covers Zotlabs\Lib\Keyutils
 */

class KeyutilsTest extends UnitTestCase {

	protected function getPubPKCS1() {
		$key = '-----BEGIN RSA PUBLIC KEY-----
MIIBCgKCAQEArXcEXQSkk25bwDxq5Ym85/OwernfOz0hgve46Jm1KXCF0+yeje8J
BDbQTsMgkF+G8eP1er3oz3E0qlIFpYrza5o6kaaLETSroTyZR5QW5S21r/QJHE+4
F08bw1zp9hrlvoOCE/g/W0mr3asO/x7LrQRKOETlZ/U6HGexTdYLyKlXJtB+VKjI
XKAHxfVLRW2AvnFj+deowS1OhTN8ECpz88xG9wnh5agoq7Uol0WZNNm0p4oR6+cd
zTPx/mBwcOoSqHLlO7ZACbx/VyD5G7mQKWfGP4b96D8FcUO74531my+aKIpLF4Io
1JN4R4a4P8tZ8BkCnMvpuq9TF1s6vEthYQIDAQAB
-----END RSA PUBLIC KEY-----';
		return str_replace(["\r", "\n"], "\r\n", $key);
	}

	protected function getPubPKCS8() {
		$key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDUKfOIkFX/Zcv6bmaTIYO6OO2g
XQOne+iPfXo6YDdrtvvQNZwW5P/fptrgBzmUBkpuc/sEEKpMV2bGhBLsWSlPBYHe
2ewwLwyzbnuHvGhc1PzwMNQ7R60ubVDQT6sBVigYGZIDBgUPjAXeqmg5qgWWh04H
8Zf/YxyoGEovWDMxGQIDAQAB
-----END PUBLIC KEY-----';
		return str_replace(["\r", "\n"], "\r\n", $key);
	}

	public function testMeToPem() {
		Keyutils::pemToMe($this->getPubPKCS8(), $m, $e);
		$gen_key = Keyutils::meToPem($m, $e);
		self::assertEquals($this->getPubPKCS8(), $gen_key);
	}

	public function testRsaToPem() {
		$rsa = new RSA();
		$rsa->setPublicKey($this->getPubPKCS8());
		$key = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS1);
		$gen_key = Keyutils::rsaToPem($key);
		self::assertEquals($gen_key, $this->getPubPKCS8());
	}

	public function testPemToRsa() {
		$rsa = new RSA();
		$rsa->setPublicKey($this->getPubPKCS1());
		$key = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_PKCS8);
		$gen_key = Keyutils::pemToRsa($key);
		self::assertEquals($gen_key, $this->getPubPKCS1());
	}

	public function testPemToMe() {
		Keyutils::pemToMe($this->getPubPKCS8(), $m, $e);
		$gen_key = new RSA();
		$gen_key->loadKey([
			'e' => new BigInteger($e, 256),
			'n' => new BigInteger($m, 256)
		]);
		self::assertEquals($gen_key->getPublicKey(), $this->getPubPKCS8());
	}

}

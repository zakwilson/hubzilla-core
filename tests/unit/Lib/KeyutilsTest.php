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

	public function testMeToPem() {
		$orig_key = '-----BEGIN PUBLIC KEY-----
MIICITANBgkqhkiG9w0BAQEFAAOCAg4AMIICCQKCAgB2Kuku7L3ElK4Et4x4Dpur
Ij5dqrcI0j7o6w39RR09ikPe43S99IVqTvTuvdOcWqkqrFffM82+GWZpco1GdTdv
wnRUCLNpDWnVU3YnwruPHDHgdybJf0gBvP1dbcOe3H3KPZVQ6WWInH6r3B3p9MCT
9AXeeC11CJCk/tNb4MPqhyG7/0MkiCJ4mNnjqQX97X2kS1mCkduVs7H6ZW//DCpR
oBft2cvCLoURUbwW0wlBnFiLH9IrRNMSCX3BZaML04NKDGSbp/n6GDTM9tX/HEf1
OH2q8vh11I64hGWsVqWu8ogitiZxXCZAZ57YlQ5ZYwqWAwHtw2XICn9ddPIHeYt7
PadOocf0L4tBdJcP7DAPuJyJSymT+zIkVD5M2h3hyORbaqpVBTMNhKyQEaTipijk
B26MS7GQSURJ1csKXSe792YV9dwBzlihX9MxT6r3sFhifUJ8PVklgHzOZ0zw9CHH
W2xGFku1h9fT+kNCi0YnTZlbmXEQKo2/Qha/nMCvA+idfDcHw9DVZNIMpH5kk3JC
GxBR2GGV8LISqKpZqZ+9AzZeqt8aCSC2/h8nq5nCWLVMTtJIiV/1GE5aEe2fR9GS
Az76YS3wXMqvWx19XE+v74sBNqhtxrZfQRfeHalDv1nUkcBkaYglQmggZ2jd+p6d
soJHIKiLs/8fMzRqLyrqZwIDAQAB
		-----END PUBLIC KEY-----';

		Keyutils::pemToMe($orig_key, $m, $e);
		$gen_key = Keyutils::meToPem($m, $e);
		self::assertEquals($orig_key, $gen_key);
	}


	public function testPemToMe() {
		$orig_key = '-----BEGIN PUBLIC KEY-----
MIICITANBgkqhkiG9w0BAQEFAAOCAg4AMIICCQKCAgB2Kuku7L3ElK4Et4x4Dpur
Ij5dqrcI0j7o6w39RR09ikPe43S99IVqTvTuvdOcWqkqrFffM82+GWZpco1GdTdv
wnRUCLNpDWnVU3YnwruPHDHgdybJf0gBvP1dbcOe3H3KPZVQ6WWInH6r3B3p9MCT
9AXeeC11CJCk/tNb4MPqhyG7/0MkiCJ4mNnjqQX97X2kS1mCkduVs7H6ZW//DCpR
oBft2cvCLoURUbwW0wlBnFiLH9IrRNMSCX3BZaML04NKDGSbp/n6GDTM9tX/HEf1
OH2q8vh11I64hGWsVqWu8ogitiZxXCZAZ57YlQ5ZYwqWAwHtw2XICn9ddPIHeYt7
PadOocf0L4tBdJcP7DAPuJyJSymT+zIkVD5M2h3hyORbaqpVBTMNhKyQEaTipijk
B26MS7GQSURJ1csKXSe792YV9dwBzlihX9MxT6r3sFhifUJ8PVklgHzOZ0zw9CHH
W2xGFku1h9fT+kNCi0YnTZlbmXEQKo2/Qha/nMCvA+idfDcHw9DVZNIMpH5kk3JC
GxBR2GGV8LISqKpZqZ+9AzZeqt8aCSC2/h8nq5nCWLVMTtJIiV/1GE5aEe2fR9GS
Az76YS3wXMqvWx19XE+v74sBNqhtxrZfQRfeHalDv1nUkcBkaYglQmggZ2jd+p6d
soJHIKiLs/8fMzRqLyrqZwIDAQAB
		-----END PUBLIC KEY-----';

		Keyutils::pemToMe($orig_key, $m, $e);

		$gen_key = new RSA();
		$gen_key->loadKey([
			'e' => new BigInteger($e, 256),
			'n' => new BigInteger($m, 256)
		]);

		self::assertEquals($gen_key->getPublicKey(), $orig_key);
	}

}

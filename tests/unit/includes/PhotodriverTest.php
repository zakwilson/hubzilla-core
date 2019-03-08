<?php

namespace Zotlabs\Tests\Unit\includes;

//use Zotlabs\Photo\PhotoGd;
use Zotlabs\Tests\Unit\UnitTestCase;
//use phpmock\phpunit\PHPMock;

/**
 * @brief Unit Test cases for include/photo/photo_driver.php file.
 */
class PhotodriverTest extends UnitTestCase {
	//use PHPMock;

	public function testPhotofactoryReturnsNullForUnsupportedType() {
		// php-mock can not mock global functions which is called by a global function.
		// If the calling function is in a namespace it would work.
		//$logger = $this->getFunctionMock(__NAMESPACE__, 'logger');
		//$logger->expects($this->once());

		//$ph = \photo_factory('', 'image/bmp');
		//$this->assertNull($ph);

		$this->markTestIncomplete('Need to mock logger(), otherwise not unit testable.');
	}

	public function testPhotofactoryReturnsPhotogdIfConfigIgnore_imagickIsSet() {
		// php-mock can not mock global functions which is called by a global function.
		// If the calling function is in a namespace it would work.
		//$gc = $this->getFunctionMock(__NAMESPACE__, 'get_config');
		// simulate get_config('system', 'ignore_imagick') configured
		//$gc->expects($this->once())->willReturn(1)

		//$ph = \photo_factory(file_get_contents('images/hz-16.png'), 'image/png');
		//$this->assertInstanceOf(PhotoGd::class, $ph);

		$this->markTestIncomplete('Need to mock get_config(), otherwise not unit testable.');
	}
}
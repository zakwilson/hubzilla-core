<?php
/*
 * Copyright (c) 2017 Hubzilla
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

namespace Zotlabs\Tests\Unit\includes;

use Zotlabs\Tests\Unit\UnitTestCase;
use Text_LanguageDetect;
//use phpmock\phpunit\PHPMock;

/**
 * @brief Unit Test cases for include/language.php file.
 *
 * @author Klaus Weidenbach
 */
class LanguageTest extends UnitTestCase {
	//use PHPMock;

	/**
	 * @dataProvider languageExamplesProvider
	 * @coversNothing
	 */
	public function testDetectLanguage($text, $langCode, $confidence) {

		// php-mock can not mock global functions which is called by a global function.
		// If the calling function is in a namespace it would work.
		//$gc = $this->getFunctionMock(__NAMESPACE__, 'get_config');
		//$gc->expects($this->once())->willReturn(10)
		//$cg = $this->getFunctionMock('Zotlabs\Lib\Config', 'Get');
		//$cg->expects($this->once())->willReturn(10);
		//$this->assertEquals($langCode, detect_language($text));


		// Can not unit test detect_language(), therefore test the used library
		// only for now to find regressions on library updates.
		$l = new Text_LanguageDetect;
		// return 2-letter ISO 639-1 (en) language code
		$l->setNameMode(2);
		$lng = $l->detectConfidence($text);

		$this->assertEquals($langCode, $lng['language']);
		$this->assertEquals($confidence, round($lng['confidence'], 6));
	}

	public function languageExamplesProvider() {
		return [
				'English' => [
						'English is a West Germanic language that was first spoken in early medieval England and is now a global lingua franca.[4][5] Named after the Angles, one of the Germanic tribes that migrated to England, it ultimately derives its name from the Anglia (Angeln) peninsula in the Baltic Sea. It is closely related to the Frisian languages, but its vocabulary has been significantly influenced by other Germanic languages, particularly Norse (a North Germanic language), as well as by Latin and Romance languages, especially French.',
						'en',
						0.078422
				],
				'German' => [
						'Deutschland ist ein Bundesstaat in Mitteleuropa. Er besteht aus 16 L??ndern und ist als freiheitlich-demokratischer und sozialer Rechtsstaat verfasst. Die Bundesrepublik Deutschland stellt die j??ngste Auspr??gung des deutschen Nationalstaates dar. Mit rund 82,8 Millionen Einwohnern (31. Dezember 2016) z??hlt Deutschland zu den dicht besiedelten Fl??chenstaaten.',
						'de',
						0.134339
				],
				'Norwegian' => [
						'Kongeriket Norge er et nordisk, europeisk land og en selvstendig stat vest p?? Den skandinaviske halv??y. Landet er langt og smalt, og kysten strekker seg langs Nord-Atlanteren, hvor ogs?? Norges kjente fjorder befinner seg. Totalt dekker det relativt tynt befolkede landet 385 000 kvadratkilometer med litt over fem millioner innbyggere (2016).',
						'no',
						0.007076
				]
		];
	}


	/**
	 * @covers ::get_language_name
	 * @dataProvider getLanguageNameProvider
	 */
	public function testGetLanguageName($lang, $name, $trans) {
		$this->assertEquals($name, get_language_name($lang));
		foreach ($trans as $k => $v) {
			//echo "$k -> $v";
			$this->assertEquals($v, get_language_name($lang, $k));
		}
	}

	public function getLanguageNameProvider() {
		return [
				'empty language code' => [
						'',
						'',
						['de' => '']
				],
				'invalid language code' => [
						'zz',
						'zz',
						['de' => 'zz']
				],
				'de' => [
						'de',
						'German',
						[
								'de' => 'Deutsch',
								'nb' => 'tysk'
						]
				],
				'de-de' => [
						'de-de',
						'German',
						[
								'de-de' => 'Deutsch',
								'nb' => 'Deutsch' // should be tysk, seems to be a bug upstream
						]
				],
				'en' => [
						'en',
						'English',
						[
								'de' => 'Englisch',
								'nb' => 'engelsk'
						]
				],
				'en-gb' => [
						'en-gb',
						'British English',
						[
								'de' => 'Englisch (Vereinigtes K??nigreich)',
								'nb' => 'engelsk (Storbritannia)'
						]
				],
				'en-au' => [
						'en-au',
						'Australian English',
						[
								'de' => 'Englisch (Australien)',
								'nb' => 'engelsk (Australia)'
						]
				],
				'nb' => [
						'nb',
						'Norwegian Bokm??l',
						[
								'de' => 'Norwegisch (Bokm??l)',
								'nb' => 'norsk bokm??l'
						]
				]
		];
	}
}

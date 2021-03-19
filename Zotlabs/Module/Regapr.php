<?php
namespace Zotlabs\Module;

use Zotlabs\Web\Controller;

class Regapr extends Controller {

	function get() {

		$o = '';

		$did2  = hex2bin(argv(1));

		if(!$did2)
			return $o;

		$o .= 'Thank you for registering!' . PHP_EOL;
		$o .= 'After your account has been approved by our administrator you will be able to login with your ID' . PHP_EOL;
		$o .= $did2 . PHP_EOL;
		$o .= 'and your provided password.';

		return $o;

	}

}

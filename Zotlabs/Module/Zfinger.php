<?php
namespace Zotlabs\Module;

use Zotlabs\Web\HTTPSig;
use Zotlabs\Lib\Libzot;

class Zfinger extends \Zotlabs\Web\Controller {

	function init() {
	
		require_once('include/zot.php');
		require_once('include/crypto.php');

		$x = zotinfo($_REQUEST);

		if($x && $x['guid'] && $x['guid_sig']) {
			$chan_hash = make_xchan_hash($x['guid'],$x['guid_sig']);
			if($chan_hash) {
				$chan = channelx_by_hash($chan_hash);
			}
		}

		$headers = [];
		$headers['Content-Type'] = 'application/json' ;
		$ret = json_encode($x);

		if($chan) {
			$headers['Digest'] = HTTPSig::generate_digest_header($ret);
			$h = HTTPSig::create_sig($headers,$chan['channel_prvkey'],'acct:' . channel_reddress($chan));
			HTTPSig::set_headers($h);
		}
		else {
			foreach($headers as $k => $v) {
				header($k . ': ' . $v);
			}
		}

		echo $ret;
		killme();
	
	}
	
}

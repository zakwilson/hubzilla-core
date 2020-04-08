<?php

namespace Zotlabs\Module;

use Zotlabs\Web\HTTPSig;

/**
 * OpenWebAuth verifier and token generator
 * See https://macgirvin.com/wiki/mike/OpenWebAuth/Home
 * Requests to this endpoint should be signed using HTTP Signatures
 * using the 'Authorization: Signature' authentication method
 * If the signature verifies a token is returned. 
 *
 * This token may be exchanged for an authenticated cookie. 
 */

class Owa extends \Zotlabs\Web\Controller {

	function init() {

		$ret = [ 'success' => false ];

		foreach([ 'REDIRECT_REMOTE_USER', 'HTTP_AUTHORIZATION' ] as $head) {
			if(array_key_exists($head,$_SERVER) && substr(trim($_SERVER[$head]),0,9) === 'Signature') {
				if($head !== 'HTTP_AUTHORIZATION') {
					$_SERVER['HTTP_AUTHORIZATION'] = $_SERVER[$head];
					continue;
				}

				$sigblock = HTTPSig::parse_sigheader($_SERVER[$head]);
				if($sigblock) {
					$keyId = $sigblock['keyId'];

					if($keyId) {

						// Hubzilla connections can have both zot6 and zot hublocs
						// The connections will usually be zot6 so match those first
						
						$r = q("select * from hubloc left join xchan on hubloc_hash = xchan_hash 
							where ( hubloc_addr = '%s' or hubloc_id_url = '%s' ) and hubloc_network = 'zot6' ",
							dbesc(str_replace('acct:','',$keyId)),
							dbesc($keyId)
						);

						// If nothing was found, try searching on any network
						
						if (! $r) {
							$r = q("select * from hubloc left join xchan on hubloc_hash = xchan_hash 
								where ( hubloc_addr = '%s' or hubloc_id_url = '%s' )",
								dbesc(str_replace('acct:','',$keyId)),
								dbesc($keyId)
							);
						}

						// If nothing was found on any network, use network discovery and create a new record
						
						if (! $r) {
							$found = discover_by_webbie(str_replace('acct:','',$keyId));
							if($found) {
								$r = q("select * from hubloc left join xchan on hubloc_hash = xchan_hash 
									where ( hubloc_addr = '%s' or hubloc_id_url = '%s' ) ",
									dbesc(str_replace('acct:','',$keyId)),
									dbesc($keyId)
								);
							}
						}
						
						if ($r) {
							foreach($r as $hubloc) {
								$verified = HTTPSig::verify(file_get_contents('php://input'),$hubloc['xchan_pubkey']);	
								if($verified && $verified['header_signed'] && $verified['header_valid']) {
									logger('OWA header: ' . print_r($verified,true),LOGGER_DATA);	
									logger('OWA success: ' . $hubloc['hubloc_addr'],LOGGER_DATA);
									$ret['success'] = true;
									$token = random_string(32);
									\Zotlabs\Lib\Verify::create('owt',0,$token,$hubloc['hubloc_network'] . ',' . $hubloc['hubloc_addr']);
									$result = '';
									openssl_public_encrypt($token,$result,$hubloc['xchan_pubkey']);
									$ret['encrypted_token'] = base64url_encode($result);
									break;
								}
								else {
									logger('OWA fail: ' . $hubloc['hubloc_id'] . ' ' . $hubloc['hubloc_addr']);
								}
							}
						}
					}
				}
			}
		}
		json_return_and_die($ret,'application/x-zot+json');
	}
}

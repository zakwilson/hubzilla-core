<?php
namespace Zotlabs\Module;

use App;
use Zotlabs\Web\Controller;
use Zotlabs\Lib\System;

class Manifest extends Controller {

	function init() {

		$ret = [
			'name' => ucfirst(System::get_platform_name()),
			'short_name' => ucfirst(System::get_platform_name()),
			'icons' => [
				[ 'src' => '/images/app/hz-72.png', 'sizes' => '72x72', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-96.png', 'sizes' => '96x96', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-128.png', 'sizes' => '128x128', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-144.png', 'sizes' => '144x144', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-152.png', 'sizes' => '152x152', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-192.png', 'sizes' => '192x192', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-348.png', 'sizes' => '384x384', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz-512.png', 'sizes' => '512x512', 'type' => 'image/png' ],
				[ 'src' => '/images/app/hz.svg', 'sizes' => '64x64', 'type' => 'image/xml+svg' ]
			],
			'scope' => '/',
			'start_url' => z_root(),
			'display' => 'standalone',
			'orientation' => 'any',
			'theme_color' => '#343a40',
			'background_color' => 'white',
			'share_target' => [
				'action' => '/rpost',
				'method' => 'POST',
				'enctype' => 'multipart/form-data',
				'params' => [
					'title' => 'title',
					'text' => 'body',
					'url' => 'url',
					'files' => [
						[ 'name' => 'userfile',
							'accept' => [ 'image/*', 'audio/*', 'video/*', 'text/*', 'application/*' ]
						]
					]
				]
			]
		];

		json_return_and_die($ret,'application/manifest+json');
	}

}

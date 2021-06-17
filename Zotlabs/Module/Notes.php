<?php
namespace Zotlabs\Module;

use App;
use Zotlabs\Web\Controller;
use Zotlabs\Lib\Apps;
use Zotlabs\Lib\Libsync;

/**
 * @brief Notes Module controller.
 */
class Notes extends Controller {

	function post() {

		if(! local_channel())
			return EMPTY_STR;

		if(! Apps::system_app_installed(local_channel(), 'Notes'))
			return EMPTY_STR;

		$ret = array('success' => true);
		if(array_key_exists('note_text',$_REQUEST)) {
			$body = escape_tags($_REQUEST['note_text']);

			// I've had my notes vanish into thin air twice in four years.
			// Provide a backup copy if there were contents previously
			// and there are none being saved now.

			if(! $body) {
				$old_text = get_pconfig(local_channel(),'notes','text');
				if($old_text)
					set_pconfig(local_channel(),'notes','text.bak',$old_text);
			}
			set_pconfig(local_channel(),'notes','text',$body);
		}

		// push updates to channel clones

		if((argc() > 1) && (argv(1) === 'sync')) {
			Libsync::build_sync_packet();
		}

		logger('notes saved.', LOGGER_DEBUG);
		json_return_and_die($ret);
	}

	function get() {
		if(! local_channel())
			return EMPTY_STR;

		if(! Apps::system_app_installed(local_channel(), 'Notes')) {
			//Do not display any associated widgets at this point
			App::$pdl = EMPTY_STR;

			$o = '<b>' . t('Notes App') . ' (' . t('Not Installed') . '):</b><br>';
			$o .= t('A simple notes app with a widget (note: notes are not encrypted)');
			return $o;
		}

		$w = new \Zotlabs\Widget\Notes;
		$arr = ['app' => true];

		return $w->widget($arr);
	}

}

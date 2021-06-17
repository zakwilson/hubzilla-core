<?php

namespace Zotlabs\Widget;

class Conversations {

	function widget($arr) {

		if (! local_channel())
			return;

		switch(argv(1)) {
			case 'inbox':
				$mailbox = 'inbox';
				$header = t('Received Messages');
				break;
			case 'outbox':
				$mailbox = 'outbox';
				$header = t('Sent Messages');
				break;
			default:
				$mailbox = 'combined';
				$header = t('Conversations');
				break;
		}

		$o = '';

		// private_messages_list() can do other more complicated stuff, for now keep it simple
		$r = self::private_messages_list(local_channel(), $mailbox, \App::$pager['start'], \App::$pager['itemspage']);

		if(! $r) {
			info( t('No messages.') . EOL);
			return $o;
		}

		$messages = [];

		foreach($r as $rr) {

			$selected = ((argc() == 3) ? intval(argv(2)) == intval($rr['id']) : $r[0]['id'] == $rr['id']);

			$messages[] = [
				'mailbox'      => $mailbox,
				'id'           => $rr['id'],
				'from_name'    => $rr['from']['xchan_name'],
				'from_url'     => chanlink_hash($rr['from_xchan']),
				'from_photo'   => $rr['from']['xchan_photo_s'],
				'to_name'      => $rr['to']['xchan_name'],
				'to_url'       => chanlink_hash($rr['to_xchan']),
				'to_photo'     => $rr['to']['xchan_photo_s'],
				'subject'      => (($rr['seen']) ? $rr['title'] : '<strong>' . $rr['title'] . '</strong>'),
				'delete'       => t('Delete conversation'),
				'body'         => $rr['body'],
				'date'         => datetime_convert('UTC',date_default_timezone_get(),$rr['created'], 'c'),
				'seen'         => $rr['seen'],
				'selected'     => ((argv(1) != 'new') ? $selected : '')
			];
		}

		$tpl = get_markup_template('mail_head.tpl');
		$o .= replace_macros($tpl, [
			'$header' => $header,
			'$messages' => $messages
		]);

		return $o;
	}

	function private_messages_list($uid, $mailbox = '', $start = 0, $numitems = 0) {

		$where = '';
		$limit = '';

		$t0 = dba_timer();

		if($numitems)
			$limit = " LIMIT " . intval($numitems) . " OFFSET " . intval($start);

		if($mailbox !== '') {
			$x = q("select channel_hash from channel where channel_id = %d limit 1",
				intval($uid)
			);

			if(! $x)
				return array();

			$channel_hash = dbesc($x[0]['channel_hash']);
			$local_channel = intval(local_channel());

			switch($mailbox) {

				case 'inbox':
					$sql = "SELECT * FROM mail WHERE channel_id = $local_channel AND from_xchan != '$channel_hash' ORDER BY created DESC $limit";
					break;

				case 'outbox':
					$sql = "SELECT * FROM mail WHERE channel_id = $local_channel AND from_xchan = '$channel_hash' ORDER BY created DESC $limit";
					break;

				case 'combined':
				default:
					$parents = q("SELECT mail.parent_mid FROM mail LEFT JOIN conv ON mail.conv_guid = conv.guid WHERE mail.mid = mail.parent_mid AND mail.channel_id = %d ORDER BY conv.updated DESC $limit",
						intval($local_channel)
					);
					break;
			}

		}

		$r = null;

		if($parents) {
			foreach($parents as $parent) {
				$all = q("SELECT * FROM mail WHERE parent_mid = '%s' AND channel_id = %d ORDER BY created DESC limit 1",
					dbesc($parent['parent_mid']),
					intval($local_channel)
				);

				if($all) {
					foreach($all as $single) {
						$r[] = $single;
					}
				}
			}
		}
		elseif($sql) {
			$r = q($sql);
		}

		if(! $r) {
			return array();
		}

		$chans = array();
		foreach($r as $rr) {
			$s = "'" . dbesc(trim($rr['from_xchan'])) . "'";
			if(! in_array($s,$chans))
				$chans[] = $s;
			$s = "'" . dbesc(trim($rr['to_xchan'])) . "'";
			if(! in_array($s,$chans))
				$chans[] = $s;
		}

		$c = q("select * from xchan where xchan_hash in (" . protect_sprintf(implode(',',$chans)) . ")");

		foreach($r as $k => $rr) {
			$r[$k]['from'] = find_xchan_in_array($rr['from_xchan'],$c);
			$r[$k]['to']   = find_xchan_in_array($rr['to_xchan'],$c);
			$r[$k]['seen'] = intval($rr['mail_seen']);
			if(intval($r[$k]['mail_obscured'])) {
				if($r[$k]['title'])
					$r[$k]['title'] = base64url_decode(str_rot47($r[$k]['title']));
				if($r[$k]['body'])
					$r[$k]['body'] = base64url_decode(str_rot47($r[$k]['body']));
			}
		}

		return $r;
	}

}


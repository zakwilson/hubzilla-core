#!/usr/bin/env php
<?php

// Convert database to support Zot 6
require_once('include/cli_startup.php');

cli_startup();

$dryrun = (($argv[1] === 'dry-run') ? true : false);

$zot = [];
$zot6 = [];

// indexed columns
$core = [
        'abconfig' => ['xchan'],
        'abook' => ['abook_xchan'],
        'app' => ['app_author'],
        'chat' => ['chat_xchan'],
        'chatpresence' => ['cp_xchan'],
        'dreport' => ['dreport_recip', 'dreport_xchan'],
        'mail' => ['from_xchan', 'to_xchan'],
        'pgrp_member' => ['xchan'],
        'source' => ['src_channel_xchan', 'src_xchan'],
        'updates' => ['ud_hash'],
        'xchat' => ['xchat_xchan'],
        'xign' => ['xchan'],
        'xlink' => ['xlink_xchan', 'xlink_link'],
        'xprof' => ['xprof_hash'],
        'xtag' => ['xtag_hash']
];

$r = dbq("SELECT channel.channel_name, channel.channel_portable_id, xchan.xchan_network FROM channel 
	LEFT JOIN xchan ON channel_portable_id = xchan_hash 
	WHERE xchan.xchan_network = 'zot' 
	AND channel.channel_removed = 0"
);

if($dryrun) {
	echo "--------------------------------------------------" . PHP_EOL;
	echo "-- This is a dry-run. No data will be modified! --" . PHP_EOL;
	echo "--------------------------------------------------" . PHP_EOL;
	sleep(3);
}

foreach($r as $rr) {

	$zot_xchan = $rr['channel_portable_id'];

	$r = q("SELECT xchan_guid FROM xchan WHERE xchan_hash = '%s' AND xchan_network = 'zot'",
		dbesc($zot_xchan)
	);

	if(!$r) {
		echo '-- ' . $zot_xchan . 'failed: zot xchan not found' . PHP_EOL;
		continue;
	}

	$guid = $r[0]['xchan_guid'];

	$r = q("SELECT xchan_hash, xchan_guid_sig FROM xchan WHERE xchan_guid = '%s' AND xchan_network = 'zot6'",
		dbesc($guid)
	);

	if(!$r) {
		echo '-- ' . $zot_xchan . 'failed: zot6 xchan not found' . PHP_EOL;
		continue;
	}

	$zot[] = $zot_xchan;
	$zot6[] = $r[0]['xchan_hash'];

	echo "-- converting indexed data for " . $rr['channel_name'] . PHP_EOL;
	foreach($core as $table => $cols) {
		foreach($cols as $col) {
			if(! $dryrun) {
				$z = q("UPDATE $table SET $col = '%s' WHERE $col = '%s'",
					dbesc($r[0]['xchan_hash']),
					dbesc($zot_xchan)
				);
			}
		}
	}
}

// columns which require a whole table scan
$core = [
	'attach' => ['creator', 'allow_cid', 'deny_cid'],
	'channel' => ['channel_allow_cid', 'channel_deny_cid'],
	'chatroom' => ['allow_cid', 'deny_cid'],
	'config' => ['v'],
	'event' => ['event_xchan', 'allow_cid', 'deny_cid'],
	'iconfig' => ['v'],
	'item' => ['owner_xchan', 'author_xchan', 'source_xchan', 'route', 'allow_cid', 'deny_cid'],
	'menu_item' => ['allow_cid', 'deny_cid'],
	'obj' => ['allow_cid', 'deny_cid'],
	'pconfig' => ['v'],
	'photo' => ['xchan', 'allow_cid', 'deny_cid'],
	'xconfig' => ['xchan', 'v']
];

foreach($core as $table => $cols) {

	$fields = implode(", ", $cols);
	$id_col = db_columns($table)[0];
	$cur_id = 0;
	$i = 0;
	
	$r = dbq("SELECT COUNT(*) AS total, MAX($id_col) AS max_id FROM $table");
	$items_total = $r[0]['total'];
	$max_id = $r[0]['max_id'];

	echo PHP_EOL;
	echo "-- converting $table table data" . PHP_EOL;

	while ($cur_id < $max_id) {

		$r = dbq("SELECT $id_col FROM $table WHERE $id_col > $cur_id ORDER BY $id_col LIMIT 100");

		foreach($r as $rr) {

			$q = '';
			$cur_id = $rr[$id_col];
			$x = dbq("SELECT $fields FROM $table WHERE $id_col = $cur_id")[0];
	
			foreach($x as $k => $v)
				$q .= (empty($q) ? "UPDATE $table SET " : ", ") . "$k = '" . dbesc(str_replace($zot, $zot6, $x[$k])) . "'";

			$q .= " WHERE $id_col = $cur_id";

			if(! $dryrun)
				dbq("$q");

			$i++;
		}

		echo "$i/$items_total\r";

	}

	echo "$i/$items_total\r";
	echo PHP_EOL;

}

echo PHP_EOL;
echo "Done!" . PHP_EOL;



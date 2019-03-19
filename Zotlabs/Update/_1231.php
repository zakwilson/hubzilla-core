<?php

namespace Zotlabs\Update;

class _1231 {

	function run() {
	
		q("START TRANSACTION");

		if(ACTIVE_DBTYPE == DBTYPE_POSTGRES) {
			$r1 = q("DROP INDEX item_uid");
			$r2 = q("DROP INDEX item_aid");
			$r3 = q("DROP INDEX item_restrict");
			$r4 = q("DROP INDEX item_flags");
			$r5 = q("DROP INDEX item_private");
			$r6 = q("DROP INDEX item_starred");
			$r7 = q("DROP INDEX item_thread_top");
			$r8 = q("DROP INDEX item_retained");
			$r9 = q("DROP INDEX item_deleted");
			$r10 = q("DROP INDEX item_type");
			$r11 = q("DROP INDEX item_hidden");
			$r12 = q("DROP INDEX item_unpublished");
			$r13 = q("DROP INDEX item_delayed");
			$r14 = q("DROP INDEX item_pending_remove");
			$r15 = q("DROP INDEX item_blocked");
			$r16 = q("DROP INDEX item_unseen");
			$r17 = q("DROP INDEX item_relay");
			$r18 = q("DROP INDEX item_verified");
			$r19 = q("DROP INDEX item_notshown");

			$r20 = q("create index item_uid_item_type on item (uid, item_type)");
			$r21 = q("create index item_uid_item_thread_top on item (uid, item_thread_top)");
			$r22 = q("create index item_uid_item_blocked on item (uid, item_blocked)");
			$r23 = q("create index item_uid_item_wall on item (uid, item_wall)");
			$r24 = q("create index item_uid_item_starred on item (uid, item_starred)");
			$r25 = q("create index item_uid_item_retained on item (uid, item_retained)");
			$r26 = q("create index item_uid_item_private on item (uid, item_private)");
			$r27 = q("create index item_uid_resource_type on item (uid, resource_type)");
			$r28 = q("create index item_item_deleted_item_pending_remove_changed on item (item_deleted, item_pending_remove, changed)");
			$r29 = q("create index item_item_pending_remove_changed on item (item_pending_remove, changed)");

			$r30 = q("create index item_thr_parent on item (thr_parent)");
			
			$r = (
				$r1 && $r2 && $r3 && $r4 && $r5 && $r6 && $r7 && $r8 && $r9 && $r10 && $r11 && $r12 && $r13 && $r14
				&& $r15 && $r16 && $r17 && $r18 && $r19 && $r20 && $r21 && $r22 && $r23 && $r24 && $r25 && $r26
				&& $r27 && $r28 && $r29 && $r30
			);
		}
		else {

			$r1 = q("ALTER TABLE item DROP INDEX item_unseen");
			$r2 = q("ALTER TABLE item DROP INDEX item_relay");
			$r3 = q("ALTER TABLE item DROP INDEX item_verified");
			$r4 = q("ALTER TABLE item DROP INDEX item_notshown");
			
			$r5 = q("ALTER TABLE item ADD INDEX thr_parent (thr_parent)");
			
			$r = ($r1 && $r2 && $r3 && $r4 && $r5);
		}

		if($r) {
			q("COMMIT");
			return UPDATE_SUCCESS;
		}

		q("ROLLBACK");
		return UPDATE_FAILED;

	}

}

[h2]collect_public_recipients[/h2]

Replace the default list of public recipients (i.e., all contacts).

Allow plugins to create a list of recipients for public messages instead of the default
of all channel connections.

Called with the following array:
                       [
                                'recipients' => [], 
                                'item' => $item, 
                                'private_envelope' => $private_envelope, 
                                'include_groups' => $include_groups
                        ];

[code]
                if(array_key_exists('public_policy',$item) && $item['public_policy'] !== 'self') {

                        $hookinfo = [
                                'recipients' => [], 
                                'item' => $item, 
                                'private_envelope' => $private_envelope, 
                                'include_groups' => $include_groups
                        ];

                        call_hooks('collect_public_recipients',$hookinfo);

                        if ($hookinfo['recipients']) {
                                $r = $hookinfo['recipients'];
                        } else {
                                $r = q("select abook_xchan, xchan_network from abook left join xchan on abook_xchan = xchan_hash where abook_channel = %d and abook_self = 0 and abook_pending = 0 and abook_archived = 0 ",
                                intval($item['uid'])
                                );
                        }

                        if($r) {

			. . .

[/code]

see: include/item.php

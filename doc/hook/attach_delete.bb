[h2]attach_delete[/h2]

Invoked when an attachment is deleted using attach_delete().

[code]
$arr = ['channel_id' => $channel_id, 'resource' => $resource, 'is_photo'=>$is_photo];
call_hooks("attach_delete",$arr);
[/code]


See include/attach.php

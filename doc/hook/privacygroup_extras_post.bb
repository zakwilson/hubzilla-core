[h2]privacygroup_extras_post[/h2]

Called as privacy group edit form is edited.

[code]
			$hookinfo = [ 'pgrp_extras' => '', 'group'=>$group['id'] ];
			call_hooks ('privacygroup_extras_post',$hookinfo);
[/code]

see: Zotlabs/Module/Group.php
see: view/tpl/privacy_groups.tpl

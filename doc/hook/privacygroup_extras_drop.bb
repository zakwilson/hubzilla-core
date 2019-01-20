[h2]privacygroup_extras_drop[/h2]

Called after privacy group is dropped

[code]
			$hookinfo = [ 'pgrp_extras' => '', 'group'=>$argv(2) ];
			call_hooks ('privacygroup_extras_drop',$hookinfo);
[/code]

see: Zotlabs/Module/Group.php
see: view/tpl/privacy_groups.tpl

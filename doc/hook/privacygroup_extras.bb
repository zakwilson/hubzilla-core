[h2]privacygroup_extras[/h2]

Add items to the Privacy Group edit form

[code]
			$hookinfo = [ 'pgrp_extras' => '', 'group'=>$argv(1) ];
			call_hooks ('privacygroup_extras',$hookinfo);
			$pgrp_extras = $hookinfo['pgrp_extras'];
[/code]

see: Zotlabs/Module/Group.php
see: view/tpl/privacy_groups.tpl

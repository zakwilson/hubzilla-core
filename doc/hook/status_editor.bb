[h2]status_editor[/h2]

Replace the default status_editor (jot).

Allow plugins to replace the default status editor in a context dependent manner.

It is fed an array of ['editor_html' => '', 'x' => $x, 'popup' => $popup, 'module' => $module]. 

All calls to the status_editor at the time of the creation of this hook have been updated
to set $module at invocation.  This allows addon developers to have a context dependent editor
based on the Hubzilla module/addon.

Calls to status_editor() are in the form of:
	status_editor($a, $x, $popup, $module).

Future module/addon developers are encouraged to set $popup and $module when invoking the
status_editor.


[code]
        $hook_info = ['editor_html' => '', 'x' => $x, 'popup' => $popup, 'module' => $module];
        call_hooks('status_editor',$hook_info);
        if ($hook_info['editor_html'] == '') {
                return hz_status_editor($a, $x, $popup);
        } else {
                return $hook_info['editor_html'];
        }

[/code]

see: include/conversation.php

[h2]system_app_installed_filter[/h2]

Allow plugins to filter the result of system_app_installed.

Code excerpt:

[code]
                        $filter_arr = [
                                'uid'=>$uid,
                                'app'=>$app,
                                'installed'=>$r
                        ];
                        call_hooks('system_app_installed_filter',$filter_arr);
                        $r = $filter_arr['installed'];
[/code]

cxref: Zotlabs/Lib/Apps.php


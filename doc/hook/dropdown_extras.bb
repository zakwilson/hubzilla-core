[h2]dropdown_extras[/h2]

Modify the dropdown menu available through the cog of items as displayed by conv_item.tpl

This hook allows plugins to add arbitrary html to the cog dropdown of thread items displayed with the conv_item.tpl template.

It is fed an array of ['item' => $item, 'dropdown_extras' => ''].  Any additions to the cog menu should be prepended/appended to
the ['dropdown_extras'] element.

[code]
$dropdown_extras_arr = [ 'item' => $item , 'dropdown_extras' => '' ];
call_hooks('dropdown_extras',$dropdown_extras_arr);
$dropdown_extras = $dropdown_extras_arr['dropdown_extras'];
[/code]

see: Zotlabs/Lib/ThreadItem.php
see: view/tpl/conv_item.tpl

[h2]item_custom[/h2]

Allow addons to create and process custom item types.

Addon authors will need to use iconfig meta data (with sharing on) or some other method
to specify and determine whether the custom item is destined for their addon.

It is fed an array of ['item' => ${item_array}, 'allow_exec' => {true/false}]

By default $arr['item']['cancel'] is set to TRUE which will abort storage of the
custom item in the item table unless the addon unsets it or sets it to false.

[code]
        if ($arr['item_type']==ITEM_TYPE_CUSTOM) {
                /* Custom items are not stored by default
                   because they require an addon to process. */
                $d['item']['cancel']=true;

                call_hooks('item_custom',$d);
        }

[/code]

see: include/items.php

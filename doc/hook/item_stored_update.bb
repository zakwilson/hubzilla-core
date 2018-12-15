[h2]item_stored_update[/h2]

Allow addons to continue processing after an item update has been stored

It is fed an array of type item (including terms and iconfig data).

[code]
        /**
         * @hooks item_stored_update
         *   Called after updated item is stored in the database.
         */
        call_hooks('item_stored_update',$arr);
[/code]

see: include/items.php

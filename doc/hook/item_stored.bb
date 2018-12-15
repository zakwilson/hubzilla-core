[h2]item_stored[/h2]

Allow addons to continue processing after an item has been stored in the event
that they need access to the item_id or other data that gets assigned during
the storage process.

It is fed an array of type item (including terms and iconfig data).

[code]
        /**
         * @hooks item_stored
         *   Called after new item is stored in the database.
         *        (By this time we have an item_id and other frequently needed info.)
         */
        call_hooks('item_stored',$arr);
[/code]

see: include/items.php

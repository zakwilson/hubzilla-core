[h2]page_meta[/h2]

Called before generating the page header.

[code]
               $pagemeta = [ 'og:title' => self::$page['title'] ];

                call_hooks('page_meta',$pagemeta);
                foreach ($pagemeta as $metaproperty => $metavalue) {
                        self::$meta->set($metaproperty,$metavalue);
                }

[/code]

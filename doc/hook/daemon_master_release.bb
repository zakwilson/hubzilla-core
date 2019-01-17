[h2]daemon_master_release[/h2]

Permit filtering or alternate methods of processing of background processes when [code] \Zotlabs\Daemon\Master::Release() [/code] is called.

Default behavior is for a new PHP process to fire immediately upon a call to Master::Summon().  This hook permits pre-emption and the ability to provide queuing or other alternatives to this procedure.

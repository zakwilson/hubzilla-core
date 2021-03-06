<?php

namespace Zotlabs\Zot6;

interface IHandler {

	function Notify($data, $hub);

	function Rekey($sender, $data, $hub);

	function Refresh($sender, $recipients, $hub, $force);

	function Purge($sender, $recipients, $hub);

}


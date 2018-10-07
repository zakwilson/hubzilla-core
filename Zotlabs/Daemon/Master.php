<?php

namespace Zotlabs\Daemon;

if(array_search( __file__ , get_included_files()) === 0) {
	require_once('include/cli_startup.php');
	array_shift($argv);
	$argc = count($argv);

	if($argc)
		Master::Release($argc,$argv);
	killme();
}



class Master {

	static public $queueworker = null;

	static public function Summon($arr) {
		proc_run('php','Zotlabs/Daemon/Master.php',$arr);
	}

	static public function Release($argc,$argv) {
		cli_startup();

		$maxworkers = get_config('system','max_queue_workers');

		if (!$maxworkers || $maxworkers == 0) {
			logger('Master: release: ' . print_r($argv,true), LOGGER_ALL,LOG_DEBUG);
                	$cls = '\\Zotlabs\\Daemon\\' . $argv[0];
                	$cls::run($argc,$argv);
			self::ClearQueue();
		} else {
			logger('Master: enqueue: ' . print_r($argv,true), LOGGER_ALL,LOG_DEBUG);
			$workinfo = ['argc'=>$argc,'argv'=>$argv];
			q("insert into config (cat,k,v) values ('queuework','%s','%s')",
				dbesc(uniqid('workitem:',true)),
				dbesc(serialize($workinfo)));
			self::Process();
		}
	}

	static public function GetWorkerID() {
		$maxworkers = get_config('system','max_queue_workers');
		$maxworkers = ($maxworkers) ? $maxworkers : 3;

		$workermaxage = get_config('system','max_queue_worker_age');
		$workermaxage = ($workermaxage) ? $workermaxage : 300;

		$workers = q("select * from config where cat='queueworkers' and k like '%s'", 'workerstarted_%');

		if (count($workers) > $maxworkers) {
			foreach ($workers as $idx => $worker) {
				$curtime = time();
				if (($time - $worker['v']) > $workermaxage) {
					$k = explode('_',$worker['k']);
					q("delete from config where cat='queueworkers' and k='%s'",
						'workerstarted_'.$k[1]);
					q("update config set k='workitem' where cat='queuework' and k='%s'",
						'workitem_'.$k[1]);
					unset($workers[$idx]);
				}
			}
			if (count($workers) > $maxworkers) {
				return false;
			}
		}
		return uniqid();

	}

	static public function Process() {

		self::$queueworker = self::GetWorkerID();

		if (!self::$queueworker) {
			logger('Master: unable to obtain worker ID.');
			killme();
		}

		set_config('queueworkers','workerstarted_'.self::$queueworker,time());

		$workersleep = get_config('system','queue_worker_sleep');
		$workersleep = ($workersleep) ? $workersleep : 5;
		cli_startup();

		$work = q("update config set k='%s' where cat='queuework' and k like '%s' limit 1",
			'workitem_'.self::$queueworker,
			dbesc('workitem:%'));
		$jobs = 0;
		while ($work) {
			$workitem = q("select * from config where cat='queuework' and k='%s'",
				'workitem_'.self::$queueworker);

			if (isset($workitem[0])) {
				$jobs++;
				$workinfo = unserialize($workitem[0]['v']);
				$argc = $workinfo['argc'];
				$argv = $workinfo['argv'];
				logger('Master: process: ' . print_r($argv,true), LOGGER_ALL,LOG_DEBUG);
				$cls = '\\Zotlabs\\Daemon\\' . $argv[0];
				$cls::run($argc,$argv);

				//Right now we assume that if we get a return, everything is OK.
				//At some point we may want to test whether the run returns true/false
				//    and requeue the work to be tried again.  But we probably want
				//    to implement some sort of "retry interval" first.

				q("delete from config where cat='queuework' and k='%s'",
					'workitem_'.self::$queueworker);
			} else {
				break;
			}
			sleep ($workersleep);
			$work = q("update config set k='%s' where cat='queuework' and k like '%s' limit 1",
				'workitem_'.self::$queueworker,
				dbesc('workitem:%'));

		}
		logger('Master: Worker Thread: queue items processed:' . $jobs);
		q("delete from config where cat='queueworkers' and k='%s'",
			'workerstarted_'.self::$queueworker);
	}	

	static public function ClearQueue() {
			$work = q("select * from config where cat='queuework' and k like '%s'",
				'workitem_%',
				dbesc('workitem%'));
			foreach ($work as $workitem) {
				$workinfo = unserialize($workitem['v']);
				$argc = $workinfo['argc'];
				$argv = $workinfo['argv'];
				logger('Master: process: ' . print_r($argv,true), LOGGER_ALL,LOG_DEBUG);
				$cls = '\\Zotlabs\\Daemon\\' . $argv[0];
				$cls::run($argc,$argv);
			}
			$work = q("delete from config where cat='queuework' and k like '%s'",
				'workitem_%',
				dbesc('workitem%'));
	}
	
}

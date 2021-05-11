<?php

namespace Zotlabs\Web;


class SessionRedis implements \SessionHandlerInterface {

        private $redis = null;


        function __construct($connection) {

                $this->redis = new \Redis();

                $credentials = parse_url($connection);

                try {
                        if (isset($credentials['path']))
                                $this->redis->connect($credentials['path']);
                        else {

                                if (isset($credentials['query']))
                                        parse_str($credentials['query'], $vars);
                                else
                                        $vars = [];

                                $this->redis->connect(
                                        (isset($credentials['scheme']) ? $credentials['scheme'] . '://' : '') . $credentials['host'],
                                        (isset($credentials['port']) ? $credentials['port'] : 6379),
                                        (isset($vars['timeout']) ? $vars['timeout'] : 1),
                                        null,
                                        0,
                                        (isset($vars['read_timeout']) ? $vars['read_timeout'] : 0)
                                );

                                if (isset($vars['auth']))
                                        $this->redis->auth($vars['auth']);
                        }
                }
                catch(\RedisException $ex) {
                        logger('Error connecting to Redis: ' . $ex->getMessage());
                }
        }


        function open($s, $n) {

                return true;
        }

        // IMPORTANT: if we read the session and it doesn't exist, create an empty record.
        // We rely on this due to differing PHP implementation of session_regenerate_id()
        // some which call read explicitly and some that do not. So we call it explicitly
        // just after sid regeneration to force a record to exist.

        function read($id) {

                if ($id) {
                        $data = $this->redis->get($id);

                        if ($data)
                                return $data;
                        else
                                $this->redis->setEx($id, 300, '');
                }

                return '';
        }


        function write($id, $data) {

                // Pretend everything is hunky-dory, even though it isn't.
                // There probably isn't anything we can do about it in any event.
                // See: https://stackoverflow.com/a/43636110

                if(! $id || ! $data)
                        return true;


                // Unless we authenticate somehow, only keep a session for 5 minutes
                // The viewer can extend this by performing any web action using the
                // original cookie, but this allows us to cleanup the hundreds or
                // thousands of empty sessions left around from web crawlers which are
                // assigned cookies on each page that they never use.

                $expire = 300;

                if($_SESSION) {
                        if(array_key_exists('remember_me',$_SESSION) && intval($_SESSION['remember_me']))
                                $expire = 60 * 60 * 24 * 365;
                        elseif(local_channel())
                                $expire = 60 * 60 * 24 * 3;
                        elseif(remote_channel())
                                $expire = 60 * 60 * 24 * 1;
                }

                $this->redis->setEx($id, $expire, $data);

                return true;
        }


        function close() {

                return true;
        }


        function destroy ($id) {

                $this->redis->del($id);

                return true;
        }


        function gc($expire) {

                return true;
        }

}

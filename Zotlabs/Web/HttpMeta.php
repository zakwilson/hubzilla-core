<?php

namespace Zotlabs\Web;


class HttpMeta {

	private $vars = null;
	private $og = null;

	function __construct() {

		$this->vars = array();
		$this->og = array();

	}

	function set($property,$value) {
		if(strpos($property,'og:') === 0)
			$this->og[$property] = $value;
		else
			$this->vars[$property] = $value;
	}

	function check_required() {
		if(
			($this->og) 
			&& array_key_exists('og:title',$this->og) 
			&& array_key_exists('og:type', $this->og) 
			&& array_key_exists('og:image',$this->og) 
			&& array_key_exists('og:url',  $this->og)
		)
			return true;
		return false;
	}

	function get_field($field) {
		if(strpos($field,'og:') === 0)
			$arr = $this->og;
		else
			$arr = $this->vars;

		if($arr && array_key_exists($field,$arr) && $arr[$field])
			return $arr[$field];
		return false;
	}


	function get() {
		$o = '';
		if($this->vars) {
			foreach($this->vars as $k => $v) {
				$o .= '<meta property="' . $k . '" content="' . urlencode($v) . '" />' . "\r\n" ;
			}
		}
		if($this->check_required()) {
			$arrayproperties = [ 'og:image' ];
			foreach($this->og as $k => $v) {
				if (in_array($k,$arrayproperties)) {
					if (is_array($v)) {
						foreach ($v as $v2) {
							$o .= '<meta property="' . $k . '" content="' . $v2 . '" />' . "\r\n" ;
						}
					} else {
						$o .= '<meta property="' . $k . '" content="' . $v . '" />' . "\r\n" ;
					}
				} else {
					$o .= '<meta property="' . $k . '" content="' . $v . '" />' . "\r\n" ;
				}
			}
		}
		if($o)
			return "\r\n" . $o;
		return $o;
	}

}

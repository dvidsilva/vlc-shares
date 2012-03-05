<?php

class X_VlcShares {
	const VERSION = '0.5.5beta';
	const VERSION_CLEAN = '0.5.5';
	private $CONFIG_PATH;
	
	
	static private $instance = null; 
	private function __construct() {
		$this->CONFIG_PATH = APPLICATION_PATH . '/configs/vlc-shares.newconfig.ini';
	}
	
	static private function i() {
		if ( is_null(self::$instance ) ) {
			self::$instance = new X_VlcShares();
		}
		return self::$instance;
	}
	static public function config() {
		return self::i()->CONFIG_PATH;
	}
	static public function version() {
		return self::VERSION;
	}
}

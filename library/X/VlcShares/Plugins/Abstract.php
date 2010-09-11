<?php

require_once 'X/Env.php';

abstract class X_VlcShares_Plugins_Abstract {

	protected $id = '';
	
	public function getId() {
		return $this->id;
	}
	
	protected function registerEvents($events = array()) {
		foreach( $events as $name => $method ) {
			X_Env::registerPlugin($name, $this, $method);
		}
	}
	
}

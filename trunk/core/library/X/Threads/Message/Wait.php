<?php 

class X_Threads_Message_Wait extends X_Threads_Message {
	
	function __construct() {
		$this->type = self::TYPE_WAIT;
	}
	
	function __toString() {
		return "WAIT: reset queue";
	}
	
	function serialize() {
		return serialize("");
	}
	
	function unserialize($serialized) {
		$this->type = self::TYPE_WAIT;
	}
	
}

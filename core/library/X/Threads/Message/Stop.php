<?php 

class X_Threads_Message_Stop extends X_Threads_Message {
	
	function __construct() {
		$this->type = self::TYPE_STOP;
	}
	
	function __toString() {
		return "STOP Thread";
	}
	
	function serialize() {
		return serialize("");
	}
	
	function unserialize($serialized) {
		$this->type = self::TYPE_STOP;
	}
	
}

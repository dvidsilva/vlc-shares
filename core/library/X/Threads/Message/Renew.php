<?php 

class X_Threads_Message_Renew extends X_Threads_Message {
	
	function __construct() {
		$this->type = self::TYPE_VOID;
	}
	
	function __toString() {
		return "RENEW Thread";
	}
	
	function serialize() {
		return serialize("");
	}
	
	function unserialize($serialized) {
		$this->type = self::TYPE_VOID;
	}
	
	
}

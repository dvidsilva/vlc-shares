<?php 


abstract class X_Threads_Message implements Serializable {

	const TYPE_VOID = 'void';
	const TYPE_STOP = 'stop';
	const TYPE_COMMAND = 'command';
	const TYPE_WAIT = 'wait';
	const TYPE_EVAL = 'eval';
	
	protected $type = self::TYPE_VOID;
	
	public function getType() {
		return $this->type;
	}
	
	public function __toString() {
		return "VOID Message sign";
	}
	
}

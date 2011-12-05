<?php 

class X_Threads_Message_Command extends X_Threads_Message {
	
	protected $runnableClass;
	protected $params;
	
	function __construct($class, $params) {
		$this->type = self::TYPE_COMMAND;
		$this->runnableClass = $class;
		$this->params = $params;
	}
	
	function __toString() {
		return "COMMAND: {$this->getRunnableClass()}, ".print_r($this->getParams(), true);
	}
	
	/**
	 * @return the $runnableClass
	 */
	public function getRunnableClass() {
		return $this->runnableClass;
	}

	/**
	 * @return the $params
	 */
	public function getParams() {
		return $this->params;
	}
	
	/* (non-PHPdoc)
	 * @see Serializable::serialize()
	 */
	public function serialize() {
		return serialize(array($this->runnableClass, $this->params));
	}

	/* (non-PHPdoc)
	 * @see Serializable::unserialize()
	 */
	public function unserialize($serialized) {
		$this->type = self::TYPE_COMMAND;
		$data = unserialize($serialized);
		list($class, $params) = $data;
		$this->runnableClass = $class;
		$this->params = $params;
	}
	
	
}

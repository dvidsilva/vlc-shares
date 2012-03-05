<?php

class Application_Model_AclResource extends Application_Model_Abstract implements ArrayAccess {
	
	private $new = true;
	protected $key = '';
	protected $class = Application_Model_AclClass::CLASS_ANONYMOUS;
	protected $generator = '';
	
	/**
	 * @return the $new
	 */
	public function isNew() {
		return $this->new;
	}

	/**
	 * @return the $key
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return the $class
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * @return the $generator
	 */
	public function getGenerator() {
		return $this->generator;
	}

	/**
	 * @param boolean $id
	 */
	public function setNew($new) {
		$this->new = $new;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key) {
		$this->key = $key;
	}

	/**
	 * @param string $class
	 */
	public function setClass($class) {
		$this->class = $class;
	}

	/**
	 * @param string $generator
	 */
	public function setGenerator($generator) {
		$this->generator = $generator;
	}
	
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return method_exists($this, "get".ucfirst($offset));
	}

	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		if ($this->offsetExists($offset) ) {
			$method = "get".ucfirst($offset);
			return $this->$method();
		} else {
			throw new Exception("Getting invalid property {{$offset}}");
		} 
	}

	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		if ( method_exists($this, "set".ucfirst($offset)) ) {
			$method = "set".ucfirst($offset);
			return $this->$method($value);
		} else {
			throw new Exception("Setting invalid property {{$offset}}");
		} 
	}

	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		// unset not implemented
	}

}


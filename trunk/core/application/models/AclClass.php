<?php

class Application_Model_AclClass extends Application_Model_Abstract implements ArrayAccess {

	const CLASS_ADMIN = 'ADMIN';
	const CLASS_BROWSE = 'BROWSE';
	const CLASS_ANONYMOUS = 'ANONYMOUS';
	
	private $new = true;
	protected $name = '';
	protected $description = '';
	
	/**
	 * @return the $new
	 */
	public function isNew() {
		return $this->new;
	}

	/**
	 * @param boolean $id
	 */
	public function setNew($new) {
		$this->new = $new;
	}
	
	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
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


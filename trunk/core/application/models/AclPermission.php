<?php

class Application_Model_AclPermission extends Application_Model_Abstract {

	protected $class = false;
	protected $username = false;
	private $new = true;
	
	

	/**
	 * @return the $class
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * @return the $idAccount
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * @return is $new
	 */
	public function isNew() {
		return $this->new;
	}

	/**
	 * @param boolean $idClass
	 */
	public function setClass($class) {
		$this->class = $class;
	}

	/**
	 * @param boolean $idAccount
	 */
	public function setUsername($username) {
		$this->username = $username;
	}
	
	/**
	 * 
	 * @param boolean $new
	 */
	public function setNew($new) {
		$this->new = $new;
	}
	
}


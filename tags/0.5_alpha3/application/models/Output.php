<?php

class Application_Model_Output {
	
	protected $_label;
	protected $_arg;
	protected $_cond_devices;
	protected $_id;
	protected $_weight;
	protected $_link;
	
	public function __construct(array $options = null) {
		if (is_array ( $options )) {
			$this->setOptions ( $options );
		}
	}
	
	public function __set($name, $value) {
		$method = 'set' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid model property' );
		}
		$this->$method ( $value );
	}
	
	public function __get($name) {
		$method = 'get' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid model property' );
		}
		return $this->$method ();
	}
	
	public function setOptions(array $options) {
		$methods = get_class_methods ( $this );
		foreach ( $options as $key => $value ) {
			$method = 'set' . ucfirst ( $key );
			if (in_array ( $method, $methods )) {
				$this->$method ( $value );
			}
		}
		return $this;
	}
	
	/**
	 * 
	 * @param $text
	 * @return Application_Model_Profile
	 */
	public function setLabel($text) {
		$this->_label = ( string ) $text;
		return $this;
	}
	
	public function getLabel() {
		return $this->_label;
	}
	
	/**
	 * 
	 * @param unknown_type $arg
	 * @return Application_Model_Profile
	 */
	public function setArg($arg) {
		$this->_arg = ( string ) $arg;
		return $this;
	}
	
	public function getArg() {
		return $this->_arg;
	}
	

	/**
	 * 
	 * @param unknown_type $cond
	 * @return Application_Model_Profile
	 */
	public function setCondDevices($cond) {
		$this->_cond_devices = $cond;
		return $this;
	}
	
	public function getCondDevices() {
		return $this->_cond_devices;
	}
	
	/**
	 * 
	 * @param unknown_type $id
	 * @return Application_Model_Profile
	 */
	public function setId($id) {
		$this->_id = ( int ) $id;
		return $this;
	}
	
	public function getId() {
		return $this->_id;
	}

	/**
	 * 
	 * @param unknown_type $weight
	 * @return Application_Model_Profile
	 */
	public function setWeight($weight) {
		$this->_weight = ( int ) $weight;
		return $this;
	}
	
	public function getWeight() {
		return $this->_weight;
	}
	
	/**
	 * 
	 * @param unknown_type $link
	 * @return Application_Model_Profile
	 */
	public function setLink($link) {
		$this->_link = $link;
		return $this;
	}
	
	public function getLink() {
		return $this->_link;
	}
	
	
}

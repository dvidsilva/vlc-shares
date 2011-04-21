<?php

class Application_Model_Megavideo {
	protected $_idVideo;
	protected $_label;
	protected $_description;
	protected $_category;
	protected $_id;
	
	public function __construct(array $options = null) {
		if (is_array ( $options )) {
			$this->setOptions ( $options );
		}
	}
	
	public function __set($name, $value) {
		$method = 'set' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid megavideo property' );
		}
		$this->$method ( $value );
	}
	
	public function __get($name) {
		$method = 'get' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid megavideo property' );
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
	
	public function setLabel($text) {
		$this->_label = ( string ) $text;
		return $this;
	}
	
	public function getLabel() {
		return $this->_label;
	}
	
	public function setDescription($email) {
		$this->_description = ( string ) $email;
		return $this;
	}
	
	public function getDescription() {
		return $this->_description;
	}
	
	public function setIdVideo($ts) {
		$this->_idVideo = $ts;
		return $this;
	}
	
	public function getIdVideo() {
		return $this->_idVideo;
	}
	
	public function setCategory($ts) {
		$this->_category = $ts;
		return $this;
	}
	
	public function getCategory() {
		return $this->_category;
	}
	
	public function setId($id) {
		$this->_id = ( int ) $id;
		return $this;
	}
	
	public function getId() {
		return $this->_id;
	}
}

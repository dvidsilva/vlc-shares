<?php

class Application_Model_YoutubeAccount {
	protected $_thumbnail;
	protected $_label;
	protected $_id;
	
	public function __construct(array $options = null) {
		if (is_array ( $options )) {
			$this->setOptions ( $options );
		}
	}
	
	public function __set($name, $value) {
		$method = 'set' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid account property' );
		}
		$this->$method ( $value );
	}
	
	public function __get($name) {
		$method = 'get' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid account property' );
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
	
	public function setThumbnail($thumb) {
		$this->_thumbnail = (string) $thumb;
		return $this;
	}
	
	public function getThumbnail() {
		return $this->_thumbnail;
	}
	
	public function setLabel($text) {
		$this->_label = ( string ) $text;
		return $this;
	}
	
	public function getLabel() {
		return $this->_label;
	}
	
	
	public function setId($id) {
		$this->_id = ( int ) $id;
		return $this;
	}
	
	public function getId() {
		return $this->_id;
	}
}

<?php

abstract class Application_Model_Abstract
{


	public function __construct(array $options = null) {
		if (is_array ( $options ))
			$this->setOptions ( $options );
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
	
	public function __set($name, $value) {
		$method = 'set' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid model property' );
		}
		return $this->$method ( $value );
	}
	
	public function __get($name) {
		$method = 'get' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( 'Invalid model property' );
		}
		return $this->$method ();
	}
	
	public function toArray() {
		$properties = get_object_vars($this);
		foreach ($properties as $key => &$value) {
			if ( is_array($value) ) {
				foreach ($value as $subkey => &$subvalue) {
					if ( $subvalue instanceof Application_Model_Abstract ) {
						$properties[$key][$subkey] = $subvalue->toArray();
					}
				}
			} else {
				if ( $value instanceof Application_Model_Abstract ) {
					$properties[$key] = $value->toArray();
				}
			}
		}
		return $properties;
		//return Zend_Json::encode($properties);
	}
	
}


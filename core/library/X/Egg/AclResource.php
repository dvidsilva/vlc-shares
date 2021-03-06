<?php 

class X_Egg_AclResource {
	
	const P_CLASS = 'class';

	static private $validProperties = array(
		// key => valid values
		
		// this class must be automatically added to anyone who has those class
		//		a | list of permission classes
		self::P_CLASS => 'regex:/[a-z_]+/i',
						
	);
	
	private $key;
	private $properties = array();
	
	function __construct($name, $properties = array()) {
		$this->key = $name;
		$this->processProperties($properties);
	}
	
	function getKey() {
		return $this->key;
	}

	protected function processProperties($properties = array()) {
	
		if ( !is_array($properties) ) {
			X_Debug::e("Properties is not an array");
			return;
		}
	
		foreach ($properties as $key => $value ) {
			$properties_ignored = true;
			// prot-type is valid
			if ( isset(self::$validProperties[$key]) ) {
				// check value
				$validValues = self::$validProperties[$key];
				@list($typeValidValues, $validValues) = @explode(':', $validValues, 2 );
	
				// typeValidValues = boolean / regex / set / ...
				switch ($typeValidValues) {
					case 'boolean':
						$checkValues = array('true', 'false', '0', '1');
						if ( array_search($value, $checkValues) ) {
							// cast to type
							$value = (bool) $value;
							$properties_ignored = false;
						} else {
							$properties_ignored = "invalid property value {{$value}}, not boolean";
						}
						break;
					case 'set':
						$checkValues = explode('|', $validValues);
						if ( array_search($value, $checkValues) ) {
							$properties_ignored = false;
						} else {
							$properties_ignored = "invalid property value {{$value}}, not in valid set";
						}
						break;
					case 'regex':
						if ( preg_match($validValues, $value) ) {
							$properties_ignored = false;
						} else {
							$properties_ignored = "invalid property value {{$value}}, format not valid";
						}
						break;
	
				}
	
			} else {
				$properties_ignored = "invalid property";
			}
	
			if ( $properties_ignored !== false ) {
				X_Debug::w("Property {{$key}} of acl-resource {{$this->getKey()}} ignored: ".$properties_ignored !== true ? $properties_ignored : 'unknown reason');
			} else {
				X_Debug::i("Valid property for acl-resource {{$this->getKey()}}: {$key} => {{$value}}");
				$this->properties[$key] = $value;
			}
		}
	
	}

	public function getProperty($property, $default) {
		return ( isset($this->properties[$property]) ? $this->properties[$property] : $default );
	}
	
	public function getClass() {
		return $this->getProperty(self::P_CLASS, Application_Model_AclClass::CLASS_BROWSE);
	}
	
}
<?php 

class X_Egg_AclClass {
	
	const P_EXTENDS = 'extends';
	const P_ONDELETE = 'onDelete';
	const P_DESCRIPTION = 'description';

	static private $validProperties = array(
		// key => valid values
		
		// this class must be automatically added to anyone who has those class
		//		a | list of permission classes
		self::P_EXTENDS => 'regex:/[a-z_]+(|[a-z_]+)*/i',
		self::P_ONDELETE => 'regex:/[a-z_]+/i',
		self::P_DESCRIPTION => 'regex:/.+/i'
						
	);
	
	private $name;
	private $properties = array();
	
	function __construct($name, $properties = array()) {
		$this->name = $name;
		$this->processProperties($properties);
	}
	
	function getName() {
		return $this->name;
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
				X_Debug::w("Property {{$key}} of acl-class {{$this->getName()}} ignored: ".$properties_ignored !== true ? $properties_ignored : 'unknown reason');
			} else {
				X_Debug::i("Valid property for acl-class {{$this->getName()}}: {$key} => {{$value}}");
				$this->properties[$key] = $value;
			}
		}
	
	}

	public function getProperty($property, $default) {
		return ( isset($this->properties[$property]) ? $this->properties[$property] : $default );
	}
	
	public function getExtends() {
		return explode("|", $this->getProperty(self::P_EXTENDS, ''));
	}
	
	public function getOnDelete() {
		$property =  $this->getProperty(self::P_ONDELETE, false);
		if ( !$property ) {
			$extends = $this->getExtends();
			if ( count($extends) ) {
				$property = $extends[0];
			}
			if ( !$property ) {
				$property = Application_Model_AclClass::CLASS_BROWSE;
			}
		}
		return $property;
	}
	
}
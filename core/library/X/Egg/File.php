<?php 

class X_Egg_File {
	
	const P_REPLACE = 'replace';
	const P_PERMISSIONS = 'permissions';
	const P_IGNOREIFNOTEXISTS = 'ignoreIfNotExists';
	const P_HALTONCOPYERROR = 'haltOnCopyError';
	const P_REMOVEREPLACEDONUNINSTALL = 'removeReplacedOnUninstall';
	const P_IGNOREUNLINKERROR = 'ignoreUnlinkError';

	static private $validProperties = array(
		// key => valid values
	
		// file can be replace if already exists
		// this file fill left after plugin unistall
		// if removeOnUninstall flag is not set to true
		self::P_REPLACE => 'boolean',
		
		// set file permission after copy
		//		chmod values are valid (4 digits only)
		self::P_PERMISSIONS => 'regex:/\d{4}/',
			
		// ignore file copy if file not exists in the package
		//		it will not halt package installation
		self::P_IGNOREIFNOTEXISTS => 'boolean',
		
		// stop files copy if this file doesn't exist
		self::P_HALTONCOPYERROR => 'boolean',
			
		// remove file on uninstall even if replace flag is set
		// this flag is not checked if replace flag is false
		self::P_REMOVEREPLACEDONUNINSTALL => 'boolean',

		// uninstall will not fail if file has not been removed
		self::P_IGNOREUNLINKERROR => 'boolean'
			
	);
	
	private $srcBasePath;
	private $path;
	private $destBasePath;
	private $properties = array();
	
	function __construct($path, $srcBasePath, $destBasePath, $properties = array()) {
		
		$this->path = $path;
		$this->srcBasePath = rtrim($srcBasePath, '\\/').'/';
		$this->destBasePath = rtrim($destBasePath, '\\/').'/';
		$this->processProperties($properties, $path);
		
	}
	
	public function getSource() {
		return $this->srcBasePath.$this->path;
	}
	
	public function getDestination() {
		return $this->destBasePath.$this->path;
	}
	
	public function getProperty($property, $default) {
		return ( isset($this->properties[$property]) ? $this->properties[$property] : $default ); 
	}
	
	protected function processProperties($properties = array(), $path = '') {

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
				X_Debug::w("Property {{$key}} of file {{$path}} ignored: ".$properties_ignored !== true ? $properties_ignored : 'unknown reason');
			} else {
				X_Debug::i("Valid property for file {{$path}}: {$key} => {{$value}}");
				$this->properties[$key] = $value;
			}
		}		
		
	}
	
}

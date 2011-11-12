<?php 

class X_PageParser_AuthDeposit_Volatile extends X_PageParser_AuthDeposit {
	
	private static $instance = null;
	
	static public function instance() {
		if ( self::$instance === null ) {
			self::$instance = new X_PageParser_AuthDeposit_Volatile();
		}
		return self::$instance;
	}
	
	private $storage = array();
	
	protected function __construct() {}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_AuthDeposit::store()
	 */
	public function store($key, $value, $validity = null) {
		$this->storage[(string) $key] = (string) $value;
	}

	/* (non-PHPdoc)
	 * @see X_PageParser_AuthDeposit::retrieve()
	 */
	public function retrieve($key) {
		if ( !array_key_exists((string) $key, $this->storage) ) {
			throw new Exception("No value stored with that key: {$key}");
		}
		return $this->storage[(string) $key];
	}

	/**
	 * Temp function to debug deposit content
	 * @deprecated
	 * @ignore
	 */
	public function dump() {
		X_Debug::i("X_PageParser_AuthDeposit_Volatile content: " . var_export($this->storage, true));
	}
	
	
}

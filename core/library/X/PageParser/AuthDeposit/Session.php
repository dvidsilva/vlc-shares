<?php 

class X_PageParser_AuthDeposit_Session extends X_PageParser_AuthDeposit {
	
	private static $instance = null;
	
	static public function instance() {
		if ( self::$instance === null ) {
			self::$instance = new X_PageParser_AuthDeposit_Volatile();
		}
		return self::$instance;
	}
	
	private $storage;
	
	protected function __construct() {
		$this->storage = new Zend_Session_Namespace(__CLASS__);
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_AuthDeposit::store()
	 */
	public function store($key, $value, $validity = null) {
		$this->storage->$key = $value;
		$this->storage->setExpirationSeconds($validity);
	}

	/* (non-PHPdoc)
	 * @see X_PageParser_AuthDeposit::retrieve()
	 */
	public function retrieve($key) {
		if ( isset($this->storage->{$key}) ) {
			throw new Exception("No value stored with that key: {$key}");
		}
		return $this->storage->{$key};
	}
	
}

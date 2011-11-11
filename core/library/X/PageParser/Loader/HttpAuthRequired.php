<?php 

class X_PageParser_Loader_HttpAuthRequired extends X_PageParser_Loader_Http {

	/**
	 * 
	 * @var X_PageParser_AuthStrategy
	 */
	private $authStrategy = null;
	/**
	 * 
	 * @var X_PageParser_AuthDeposit
	 */
	private $authDeposit = null;

	static public function instance() {
		return new self();
	}	
	
	public function __construct() {}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Loader::getPage()
	 */
	public function getPage($uri) {
		
		$this->getHttpClient()
				->setUri($uri)
				->setConfig(array('storeresponse' => true));
		
		// storeresponse must be enabled
		$this->getAuthStrategy()->prepareLoader($this)->performStrategy();
		
		if ( is_null($this->getHttpClient()->getLastResponse()) ) {
			$this->getHttpClient()->request();
		}
		
		// get the last response from the client (strategy can execute more than 1 request)
		return $this->getHttpClient()->getLastResponse()->getBody();
		
	}
	
	/**
	 * @return the $authStrategy
	 */
	public function getAuthStrategy() {
		if ( $this->authStrategy === null ) {
			$this->authStrategy = new X_PageParser_AuthStrategy_Dummy();
		}
		return $this->authStrategy;
	}

	/**
	 * @param X_PageParser_AuthStrategy $authStrategy
	 * @return X_PageParser_HttpAuthRequired
	 */
	public function setAuthStrategy($authStrategy) {
		$this->authStrategy = $authStrategy;
		return $this;
	}

	/**
	 * @return the $authDeposit
	 */
	public function getAuthDeposit() {
		if ( $this->authDeposit === null ) {
			$this->authDeposit = X_PageParser_AuthDeposit_Volatile::instance();
		} 
		return $this->authDeposit;
	}

	/**
	 * @param X_PageParser_AuthDeposit $authDeposit
	 * @return X_PageParser_HttpAuthRequired
	 */
	public function setAuthDeposit($authDeposit) {
		$this->authDeposit = $authDeposit;
		return $this;
	}

	
	
	
}


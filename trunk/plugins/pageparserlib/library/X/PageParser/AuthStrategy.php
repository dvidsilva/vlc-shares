<?php 

abstract class X_PageParser_AuthStrategy {
	
	/**
	 * @var X_PageParser_Loader_HttpAuthRequired
	 */
	protected $loader = null;
	
	/**
	 * @return X_PageParser_Loader_HttpAuthRequired
	 * @throws Exception
	 */
	protected function getLoader() {
		if ( $this->loader === null ) throw new Exception("No valid loader set");
		return $this->loader;
	}
	
	/**
	 * Prepare the loader for the Strategy (and the strategy for the loader)
	 * MUST be called before performStrategy and isAuthenticated
	 * @param X_PageParser_Loader_HttpAuthRequired $loader
	 * @return X_PageParser_AuthStrategy
	 */
	public function prepareLoader(X_PageParser_Loader_HttpAuthRequired $loader) {
		$this->loader = $loader;
		return $this;
	}
	/**
	 * Perform the page request performing the authentication strategy if needed
	 * prepareLoader must be called before the strategy is triggered
	 */
	abstract public function performStrategy();
	/**
	 * Check if loader is ready for authenticated requests
	 * @return boolean
	 * @throws Exception if AuthStrategy cannot decide if true or false
	 */
	abstract public function isAuthenticated();
	
}

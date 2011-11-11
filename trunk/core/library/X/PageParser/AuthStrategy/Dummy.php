<?php 

/**
 * Dummy AuthStrategy. Always authenticated. Using this is the same as use a
 * normal Http Loader without any auth strategy
 * @author ximarx
 *
 */
class X_PageParser_AuthStrategy_Dummy extends X_PageParser_AuthStrategy {

	/* (non-PHPdoc)
	 * @see X_PageParser_AuthStrategy::performStrategy()
	 */
	public function performStrategy() {
		$this->getLoader()->getHttpClient()->request();
	}

	/* (non-PHPdoc)
	 * @see X_PageParser_AuthStrategy::isAuthenticated()
	 */
	public function isAuthenticated() {
		$this->getLoader();
		return true;
	}
}

<?php

class X_VlcShares_Plugins_Helper_HosterSSWrapper extends X_VlcShares_Plugins_Helper_Hoster {
	
	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_Hoster
	 */
	private $hoster;
	
	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_HostInterface
	 */
	private $lastPositiveMatch = null;
	
	public function __construct(X_VlcShares_Plugins_Helper_Hoster $realHoster) {
		$this->hoster = $realHoster;
	}
	
	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Helper_Hoster::findHoster()
	 */
	public function findHoster($url) {
		$this->lastPositiveMatch = $this->hoster->findHoster($url); 
		return $this->lastPositiveMatch;
	}

	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Helper_Hoster::getHoster()
	 */
	public function getHoster($id) {
		$this->lastPositiveMatch = $this->hoster->getHoster($id);
		return $this->lastPositiveMatch;
	}

	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Helper_Hoster::getHosters()
	 */
	public function getHosters() {
		return $this->hoster->getHosters();
	}

	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Helper_Hoster::isRegisteredHoster()
	 */
	public function isRegisteredHoster($id) {
		return $this->hoster->isRegisteredHoster($id);
	}

	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Helper_Hoster::registerHoster()
	 */
	public function registerHoster(X_VlcShares_Plugins_Helper_HostInterface $hoster, $id = null, $pattern = null) {
		return $this->hoster->registerHoster($hoster, $id, $pattern);
	}

	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Helper_Hoster::unregisterHoster()
	 */
	public function unregisterHoster($id) {
		return $this->hoster->unregisterHoster($id);
	}
	
	/**
	 * Return the last positive match done by 
	 * 		- findHoster()
	 * 		- getHoster()
	 * @throws Exception
	 * @return X_VlcShares_Plugins_Helper_HostInterface
	 */
	public function getLastPositiveMatch() {
		if ( $this->lastPositiveMatch == null ) {
			throw new Exception("No positive match done");
		}
		return $this->lastPositiveMatch;
	}
	
	
}

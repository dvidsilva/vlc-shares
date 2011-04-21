<?php 


class X_VlcShares_Plugins_Helper_Hoster extends X_VlcShares_Plugins_Helper_Abstract {
	
	
	private $hosters = array();
	
	public function __construct($options = array()) {
	}
	
	public function getHosters() {
		$t_hosters = array();
		foreach ($this->hosters as $id => $info) {
			$t_hosters[$id] = $info['pattern'];
		}
		return $t_hosters;
	}
	
	/**
	 * add a new HostInterface inside the supported hoster
	 * @param X_VlcShares_Plugins_Helper_HostInterface $hoster concrete implementation of X_VlcShares_Plugins_Helper_HostInterface
	 * @param string $id an overloaded hoster id
	 * @param string $pattern an overloaded hoster pattern
	 * @return void
	 */
	public function registerHoster(X_VlcShares_Plugins_Helper_HostInterface $hoster, $id = null, $pattern = null) {
		if ( $id === null ) {
			$id = $hoster->getId();
		}
		if ( $pattern === null ) {
			$pattern = $hoster->getPattern();
		}
		$this->hosters[$id] = array(
			'pattern' => $pattern,
			'hoster' => $hoster
		);
	}
	
	/**
	 * remove a register hoster from the lit
	 * @param string $id host id
	 */
	public function unregisterHoster($id) {
		if ( $this->isRegisteredHoster($id) ) {
			unset($this->hoster[$id]);
		}
	}
	
	/**
	 * check if an hoster for the $id is registered
	 * @param string $id
	 * @return boolean
	 */
	public function isRegisteredHoster($id) {
		return array_key_exists($id, $this->hosters);
	}
	
	/**
	 * get an host handler for the $url type
	 * @param $url
	 * @return X_VlcShares_Plugins_Helper_HostInterface
	 */
	public function findHoster($url) {
		/* @var $hosterConcrete X_VlcShares_Plugins_Helper_HostInterface */
		$hosterConcrete = null;
		foreach ($this->hosters as $hoster) {
			if ( preg_match($hoster['pattern'], $url) ) {
				$hosterConcrete = $hoster['hoster'];
				break;
			}
		}
		if ( $hosterConcrete === null ) {
			throw new Exception("There is no HostInterface who can handle the request for {$url}");
		}
		return $hosterConcrete;
	}

	/**
	 * get an hoster by his id
	 * @param string 
	 * @return X_VlcShares_Plugins_Helper_HostInterface
	 * @throws Exception is not valid $id
	 */
	public function getHoster($id) {
		if ( $this->isRegisteredHoster($id) ) {
			return $this->hosters[$id]['hoster'];
		} else {
			throw new Exception("Invalid hoster id");
		}
	}
}


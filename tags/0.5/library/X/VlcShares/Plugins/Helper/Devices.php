<?php

require_once ('X/VlcShares/Plugins/Helper/Abstract.php');

class X_VlcShares_Plugins_Helper_Devices extends X_VlcShares_Plugins_Helper_Abstract {

	const DEVICE_WIIMC = 0;
	const DEVICE_ANDROID = 1;
	const DEVICE_IPHONE = 2;
	const DEVICE_IPAD = 3;
	const DEVICE_PC = 100;
	
	function __construct() {
		X_Debug::i("User agent: {$_SERVER['HTTP_USER_AGENT']}");
	}
	
	/**
	 * Check if page request is made with wiimc.
	 * If $version is provided, check if this is
	 * the same version provided
	 * @param string $version
	 * @return boolean
	 */
	public function isWiimc($version = false) {
		if ( $version === false ) {
			return (strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false);
		} else {
			return ( $this->isWiimc() && (strpos($_SERVER['HTTP_USER_AGENT'], $version) !== false ) );
		}
	}
	
	/**
	 * return true if $version is >= of wiimc user-agent's version
	 * Warning: if not wiimc return true
	 * @param string $version
	 * @return boolean
	 */
	public function isWiimcBeforeVersion($version) {
		return version_compare($version, $this->getWiimcVersion(), '>=');
	}
	
	/**
	 * Get wiimc version if request is made by wiimc
	 * or null otherwise
	 * @return string
	 */
	public function getWiimcVersion() {
		if ( $this->isWiimc() ) {
			$split = explode('/', $_SERVER['HTTP_USER_AGENT']);
			return @$split[1];
		} else {
			return null;
		}
	}
	
	public function isAndroid($deviceClass = false) {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		// HTC Desire 2.2 has user agent = Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Safari/530.17
		return (
			strpos($userAgent, 'Android') !== false 
			/* || $userAgent == 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Safari/530.17' */ 
		);
	}
	
	public function getAndroidClass() {
		if ( $this->isAndroid() ) {
			// TODO
			// i have to find a way to 
			// categorize android devices 
			// (resolution/codec/features based categorization)
			return;
		} else {
			return;
		}
	}
	
	/**
	 * Check if request is made by pc
	 * (the check is performed as a fallback
	 * for other devices)
	 * @return boolean
	 */
	public function isPc() {
		return (!$this->isWiimc() && !$this->isAndroid());
	}
	
	/**
	 * Get the type of device who made the request
	 * @return int (a value of DEVICE_XXX const)
	 */
	public function getDeviceType() {
		if ( $this->isWiimc() ) 
			return self::DEVICE_WIIMC;
		elseif ( $this->isAndroid() )
			return self::DEVICE_ANDROID;
		// TODO
		/* // not ready for this yet
		elseif ( $this->isIPhone())
			return self::DEVICE_IPHONE;
		elseif ( $this->isIPad())
			return self::DEVICE_IPHONE;
		*/
		else
			return self::DEVICE_PC;
	}
	
}

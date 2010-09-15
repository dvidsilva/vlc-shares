<?php

require_once ('library/X/VlcShares/Plugins/Helper/Abstract.php');

class X_VlcShares_Plugins_Helper_Devices extends X_VlcShares_Plugins_Helper_Abstract {

	const DEVICE_WIIMC = 0;
	const DEVICE_ANDROID = 1;
	const DEVICE_IPHONE = 2;
	const DEVICE_IPAD = 3;
	const DEVICE_PC = 100;
	
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
	 * Get wiimc version if request is made by wiimc
	 * or null otherwise
	 * @return string
	 */
	public function getWiimcVersion() {
		if ( $this->isWiimc() ) {
			$split = split($_SERVER['HTTP_USER_AGENT'], '/');
			return @$split[1];
		} else {
			return null;
		}
	}
	
	public function isAndroid($deviceClass = false) {
		// TODO
		// check if android....
		// i have to check milestone user-agent
		return false;
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

<?php

require_once ('X/VlcShares/Plugins/Helper/Abstract.php');

class X_VlcShares_Plugins_Helper_Devices extends X_VlcShares_Plugins_Helper_Abstract {

	const DEVICE_WIIMC = 0;
	const DEVICE_ANDROID = 1;
	const DEVICE_IPHONE = 2;
	const DEVICE_IPAD = 3;
	const DEVICE_VLC = 4;
	const DEVICE_PC = 100;

	/**
	 * @var Application_Model_Device|false|null
	 */
	private $device = null;
	
	/**
	 * 
	 * @var Zend_Config
	 */
	private $options = null;
	
	function __construct(Zend_Config $options) {
		
		X_Debug::i("User agent: {$_SERVER['HTTP_USER_AGENT']}");
		$this->options = $options;
		
	}
	
	/**
	 * Get device features
	 * 
	 * @return Application_Model_Device
	 */
	public function getDevice() {
		if ( $this->device === null ) {
			
			$this->device = false;
			$devices = Application_Model_DevicesMapper::i()->fetchAll();
			
			/* @var Application_Model_Device $device */
			foreach ($devices as $device) {
				// if exact do an == comparison
				if ( ($device->isExact() && $device->getPattern() == $_SERVER['HTTP_USER_AGENT'])
					// otherwise a regex match
						|| (!$device->isExact() && preg_match($device->getPattern(), $_SERVER['HTTP_USER_AGENT'] ) > 0 ) ) {
					
					// valid $device found;
					$this->device = $device;
					break;
						
				} // false + 0 matches
			}
			
			
			if ( $this->device === false ) {
				// load things from default
				
				$this->device = new Application_Model_Device();
				if ( X_VlcShares_Plugins::broker()->isRegistered('wiimc') ) {
					$this->device->setGuiClass($this->options->get('gui', 'X_VlcShares_Plugins_WiimcPlxRenderer' ));
				} else {
					$this->device->setGuiClass($this->options->get('gui', 'X_VlcShares_Plugins_WebkitRenderer' ));
				}
				
				$this->device->setIdProfile($this->options->get('profile', 1))
					->setIdOutput(1) // FIXME remove this after profiles+outputs
					->setLabel("Unknown device")
					;
			}
		}
		
		return $this->device;
	}	
	
	/**
	 * @return string
	 */
	public function getDefaultDeviceGuiClass() {
		return $this->getDevice()->getGuiClass();
	}
	
	/**
	 * @return int
	 */
	public function getDefaultDeviceIdProfile() {
		return $this->getDevice()->getIdProfile();
	}
	
	/**
	 * @return int
	 */
	public function getDefaultDeviceIdOutput() {
		return $this->getDevice()->getIdOutput();
	}
	
	public function getDeviceLabel() {
		return $this->getDevice()->getLabel();
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
			@list(, $versionRaw) = explode('/', $_SERVER['HTTP_USER_AGENT']);
			// version 1.1.1 change useragent type
			@list($version, $ios) = explode(' ', $versionRaw);
			return str_replace('+', '', $version);
		} else {
			return null;
		}
	}
	
	/**
	 * return true if device is WiiMC with Enhancement Pack V+
	 * @return boolean
	 */
	public function isWiimcEnhanced() {
		if ( $this->isWiimc() ) {
			$split = explode('/', $_SERVER['HTTP_USER_AGENT']);
			return (strpos(@$split[1], '+') !== false);
		} else {
			return false;
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
	
	public function isVlc() {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		return ( stripos($userAgent, 'vlc/') !== false	);
	}
	
	public function getVlcVersion() {
		if ( $this->isVlc() ) {
			@list(, $version) = explode('/', $_SERVER['HTTP_USER_AGENT']);
			return $version;
		} else {
			return null;
		}
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
		elseif ( $this->isVlc() )
			return self::DEVICE_VLC;
		else
			return self::DEVICE_PC;
	}
	
	
}

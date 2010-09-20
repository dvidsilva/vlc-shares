<?php 

require_once 'X/VlcShares/Plugins/Helper/Interface.php';
require_once 'X/VlcShares/Plugins/Helper/Abstract.php';

class X_VlcShares_Plugins_Helper_Broker {
	
	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_Mediainfo
	 */
	private $mediainfo;
	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_GSpot
	 */
	private $gspot;

	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_MPlayer
	 */
	private $mplayer;

	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_Stream
	 */
	private $stream;
	
	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_Devices
	 */
	private $devices;
	
	private $_helpers = array();
	
	public function __construct() {
		$this->init();
	}
	public function init() {
		$this->mediainfo = new X_VlcShares_Plugins_Helper_Mediainfo();
		$this->gspot = new X_VlcShares_Plugins_Helper_GSpot();
		$this->mplayer = new X_VlcShares_Plugins_Helper_MPlayer();
		$this->devices = new X_VlcShares_Plugins_Helper_Devices();
		$this->stream = new X_VlcShares_Plugins_Helper_Stream();
	}
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Mediainfo
	 */
	public function mediainfo() { return $this->mediainfo; }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_GSpot
	 */
	public function gspot() { return $this->gspot; }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_MPlayer
	 */
	public function mplayer() { return $this->mplayer; }

	/**
	 * @return X_VlcShares_Plugins_Helper_Stream
	 */
	public function stream() { return $this->stream; }
	
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Devices
	 */
	public function devices() { return $this->devices; }
	
	
	public function registerHelper($helperName, X_VlcShares_Plugins_Helper_Interface  $helperObj) {
		$this->_helpers[$helperName] = $helperObj;
		X_Debug::i("Plugin helper $helperName registered");
	}
	
	/**
	 * 
	 * @param string $helperName
	 * @return X_VlcShares_Plugins_Helper_Interface
	 */
	public function helper($helperName) {
		if ( array_key_exists($helperName, $this->_helpers) ) {
			return $this->_helpers[$helperName];
		} else {
			throw new Exception("Plugin helper '$helperName' unknown");
		}
	}
}
<?php 

require_once 'X/VlcShares/Plugins/Helper/Interface.php';
require_once 'X/VlcShares/Plugins/Helper/Abstract.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_Helper_Broker {

	
	private $_helpers = array();
	
	public function __construct(Zend_Config $options) {
		$this->init($options);
	}
	public function init(Zend_Config $options) {
		
		$language = new X_VlcShares_Plugins_Helper_Language($options->get('language', new Zend_Config(array())));
		$ffmpeg = new X_VlcShares_Plugins_Helper_FFMpeg($options->get('ffmpeg', new Zend_Config(array())));
		$devices = new X_VlcShares_Plugins_Helper_Devices($options->get('devices', new Zend_Config(array())));
		$stream = new X_VlcShares_Plugins_Helper_Stream($options->get('stream', new Zend_Config(array())));
		$paginator = new X_VlcShares_Plugins_Helper_Paginator($options->get('paginator', new Zend_Config(array())));
		$hoster = new X_VlcShares_Plugins_Helper_Hoster();
		$rtmpdump = new X_VlcShares_Plugins_Helper_RtmpDump($options->get('rtmpdump', new Zend_Config(array())));
		$sopcast = new X_VlcShares_Plugins_Helper_SopCast($options->get('sopcast', new Zend_Config(array())));
		//$vlc = new X_VlcShares_Plugins_Helper_Vlc($options->get('vlc', new Zend_Config(array())));
		$streamer = new X_VlcShares_Plugins_Helper_Streamer($options->get('streamer', new Zend_Config(array())));
		$acl = X_VlcShares_Plugins_Helper_Acl::instance($options->get('acl', new Zend_Config(array())));
		
		$this
			//->registerHelper('vlc', $vlc, true)
			->registerHelper('language', $language, true)
			->registerHelper('ffmpeg', $ffmpeg, true)
			->registerHelper('devices', $devices, true)
			->registerHelper('stream', $stream, true)
			->registerHelper('paginator', $paginator, true)
			->registerHelper('hoster', $hoster, true)
			->registerHelper('rtmpdump', $rtmpdump, true)
			->registerHelper('streamer', $streamer, true)
			->registerHelper('sopcast', $sopcast, true)
			->registerHelper('acl', $acl, true);
	}
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Vlc
	 */
	public function vlc() { return $this->helper(__FUNCTION__); }

	/**
	 * @return X_VlcShares_Plugins_Helper_Language
	 */
	public function language() { return $this->helper(__FUNCTION__); }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_FFMpeg
	 */
	public function ffmpeg() { return $this->helper(__FUNCTION__); }

	/**
	 * @return X_VlcShares_Plugins_Helper_Stream
	 */
	public function stream() { return $this->helper(__FUNCTION__); }
	
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Devices
	 */
	public function devices() { return $this->helper(__FUNCTION__); }

	/**
	 * @return X_VlcShares_Plugins_Helper_Paginator
	 */
	public function paginator() { return $this->helper(__FUNCTION__); }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Hoster
	 */
	public function hoster() { return $this->helper(__FUNCTION__); }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_RtmpDump
	 */
	public function rtmpdump() { return $this->helper(__FUNCTION__); }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_SopCast
	 */
	public function sopcast() { return $this->helper(__FUNCTION__); }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Streamer
	 */
	public function streamer() { return $this->helper(__FUNCTION__); }

	/**
	 * @return X_VlcShares_Plugins_Helper_Acl
	 */
	public function acl() { return $this->helper(__FUNCTION__); }
	
	
	/**
	 * Proxy function call to 
	 * self::helper()
	 * @param string $method helper name
	 * @param array $argv ignored
	 * @return X_VlcShares_Plugins_Helper_Abstract
	 * @throws Exception if helper name not found
	 */
	public function __call($method, $argv) {
		return $this->helper($method);
	}
	
	
	/**
	 * Register a new helper in the list
	 * Uses fluent interface
	 * @param string $helperName
	 * @param X_VlcShares_Plugins_Helper_Interface $helperObj
	 * @return X_VlcShares_Plugins_Helper_Broker
	 */
	public function registerHelper($helperName, X_VlcShares_Plugins_Helper_Interface  $helperObj, $silent = false) {
		$this->_helpers[$helperName] = $helperObj;
		if ( !$silent ) X_Debug::i("Plugin helper $helperName registered");
		return $this;
	}
	
	/**
	 * Return the list of registered helpers
	 * @return array of helperName => helperObj
	 */
	public function getHelpers() {
		return $this->_helpers;
	}
	
	/**
	 * Check if helper is registered
	 * @param string $helperName helper name
	 * @return boolean
	 */
	public function isRegistered($helperName) {
		return (isset($this->_helpers[$helperName]));
	}
	
	/**
	 * Get an helper by name
	 * 
	 * @param string $helperName
	 * @return X_VlcShares_Plugins_Helper_Interface
	 * @throws Exception if helper name not found
	 */
	public function helper($helperName) {
		if ( array_key_exists($helperName, $this->_helpers) ) {
			return $this->_helpers[$helperName];
		} else {
			throw new Exception("Plugin helper '$helperName' unknown");
		}
	}
}
<?php 

require_once 'X/VlcShares/Plugins/Helper/Interface.php';
require_once 'X/VlcShares/Plugins/Helper/Abstract.php';
require_once 'Zend/Config.php';

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
	 * @var X_VlcShares_Plugins_Helper_FFMpeg
	 */
	private $ffmpeg;

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
	
	public function __construct(Zend_Config $options) {
		$this->init($options);
	}
	public function init(Zend_Config $options) {
		
		$this->mediainfo = new X_VlcShares_Plugins_Helper_Mediainfo($options->get('mediainfo', new Zend_Config(array())));
		$this->gspot = new X_VlcShares_Plugins_Helper_GSpot($options->get('gspot', new Zend_Config(array())));
		$this->ffmpeg = new X_VlcShares_Plugins_Helper_FFMpeg($options->get('ffmpeg', new Zend_Config(array())));
		$this->devices = new X_VlcShares_Plugins_Helper_Devices($options->get('devices', new Zend_Config(array())));
		$this->stream = new X_VlcShares_Plugins_Helper_Stream($options->get('stream', new Zend_Config(array())));
		
		$this->registerHelper('mediainfo', $this->mediainfo)
			->registerHelper('gspot', $this->gspot)
			->registerHelper('ffmpeg', $this->ffmpeg)
			->registerHelper('devices', $this->devices)
			->registerHelper('stream', $this->stream);
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
	 * @return X_VlcShares_Plugins_Helper_FFMpeg
	 */
	public function ffmpeg() { return $this->ffmpeg; }

	/**
	 * @return X_VlcShares_Plugins_Helper_Stream
	 */
	public function stream() { return $this->stream; }
	
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Devices
	 */
	public function devices() { return $this->devices; }
	
	/**
	 * Register a new helper in the list
	 * Uses fluent interface
	 * @param string $helperName
	 * @param X_VlcShares_Plugins_Helper_Interface $helperObj
	 * @return X_VlcShares_Plugins_Helper_Broker
	 */
	public function registerHelper($helperName, X_VlcShares_Plugins_Helper_Interface  $helperObj) {
		$this->_helpers[$helperName] = $helperObj;
		X_Debug::i("Plugin helper $helperName registered");
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
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
	 * @var X_VlcShares_Plugins_Helper_Language
	 */
	private $language;

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
	
	/**
	 * @var X_VlcShares_Plugins_Helper_Paginator
	 */
	private $paginator;
	
	/**
	 * @var X_VlcShares_Plugins_Helper_Hoster
	 */
	private $hoster;

	/**
	 * @var X_VlcShares_Plugins_Helper_RtmpDump
	 */
	private $rtmpdump;

	/**
	 * @var X_VlcShares_Plugins_Helper_SopCast
	 */
	private $sopcast;
	
	private $_helpers = array();
	
	public function __construct(Zend_Config $options) {
		$this->init($options);
	}
	public function init(Zend_Config $options) {
		
		$this->language = new X_VlcShares_Plugins_Helper_Language($options->get('language', new Zend_Config(array())));
		$this->ffmpeg = new X_VlcShares_Plugins_Helper_FFMpeg($options->get('ffmpeg', new Zend_Config(array())));
		$this->devices = new X_VlcShares_Plugins_Helper_Devices($options->get('devices', new Zend_Config(array())));
		$this->stream = new X_VlcShares_Plugins_Helper_Stream($options->get('stream', new Zend_Config(array())));
		$this->paginator = new X_VlcShares_Plugins_Helper_Paginator($options->get('paginator', new Zend_Config(array())));
		$this->hoster = new X_VlcShares_Plugins_Helper_Hoster();
		$this->rtmpdump = new X_VlcShares_Plugins_Helper_RtmpDump($options->get('rtmpdump', new Zend_Config(array())));
		$this->sopcast = new X_VlcShares_Plugins_Helper_SopCast($options->get('sopcast', new Zend_Config(array())));
		
		$this
			->registerHelper('language', $this->language, true)
			->registerHelper('ffmpeg', $this->ffmpeg, true)
			->registerHelper('devices', $this->devices, true)
			->registerHelper('stream', $this->stream, true)
			->registerHelper('paginator', $this->paginator, true)
			->registerHelper('hoster', $this->hoster, true)
			->registerHelper('rtmpdump', $this->rtmpdump, true)
			->registerHelper('sopcast', $this->sopcast, true);
	}
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Language
	 */
	public function language() { return $this->language; }
	
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
	 * @return X_VlcShares_Plugins_Helper_Paginator
	 */
	public function paginator() { return $this->paginator; }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_Hoster
	 */
	public function hoster() { return $this->hoster; }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_RtmpDump
	 */
	public function rtmpdump() { return $this->rtmpdump; }
	
	/**
	 * @return X_VlcShares_Plugins_Helper_RtmpDump
	 */
	public function sopcast() { return $this->sopcast; }
	
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
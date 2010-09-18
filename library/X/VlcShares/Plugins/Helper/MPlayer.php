<?php

require_once ('X/VlcShares/Plugins/Helper/Abstract.php');
require_once ('X/VlcShares/Plugins/Helper/StreaminfoInterface.php');

class X_VlcShares_Plugins_Helper_MPlayer extends X_VlcShares_Plugins_Helper_Abstract implements X_VlcShares_Plugins_Helper_StreaminfoInterface {
	
	private $_location = null;
	private $_fetched = false;
	
	/**
	 * Set location source
	 * 
	 * @param $location the source
	 * @return X_VlcShares_Plugins_Helper_MPlayer
	 */
	function setLocation($location) {
		if ( $this->_location != $location ) {
			$this->_location = $location;
			$this->_fetched = false;
		}
		return $this;
	}

	/**
	 * 
	 */
	public function getInfos() {
		
	}

	/**
	 * 
	 */
	public function getVideosInfo() {
		
	}

	/**
	 * @param unknown_type $index
	 */
	public function getVideoCodecName($index = 0) {
		
	}

	/**
	 * @param unknown_type $index
	 */
	public function getVideoCodecType($index = 0) {
		
	}

	/**
	 * 
	 */
	public function getVideoStreamsNumber() {
		
	}

	/**
	 * 
	 */
	public function getAudiosInfo() {
		
	}

	/**
	 * @param unknown_type unknown_type $index
	 */
	public function getAudioCodecName($index = 0) {
		
	}

	/**
	 * @param unknown_type unknown_type $index
	 */
	public function getAudioCodecType($index = 0) {
		
	}

	/**
	 * 
	 */
	public function getAudioStreamsNumber() {
		
	}

	/**
	 * 
	 */
	public function getSubsInfo() {
		
	}

	/**
	 * 
	 */
	public function getSubsNumber() {
		
	}

	/**
	 * @param unknown_type unknown_type $index
	 */
	public function getSubFormat($index = 0) {
		
	}

	/**
	 * @param unknown_type unknown_type $index
	 */
	public function getSubLanguage($index = 0) {
		
	}


	
	
	
	
}


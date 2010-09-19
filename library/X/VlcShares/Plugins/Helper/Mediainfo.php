<?php

require_once ('X/VlcShares/Plugins/Helper/Abstract.php');
require_once ('X/VlcShares/Plugins/Helper/StreaminfoInterface.php');

/**
 * Fetch info about a FILE (remote locations
 * aren't allowed. Mediainfo return blank string
 * for remote files)
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Helper_Mediainfo extends X_VlcShares_Plugins_Helper_Abstract implements X_VlcShares_Plugins_Helper_StreaminfoInterface {
	
	
	private $_location = null;
	private $_fetched = false;
	
	/**
	 * Set location source
	 * 
	 * @param $location the source
	 * @return X_VlcShares_Plugins_Helper_Mediainfo
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
		$this->fetch();
		return $this->_fetched;
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
	 * @param unknown_type $index
	 */
	public function getAudioCodecName($index = 0) {
		
	}

	/**
	 * @param unknown_type $index
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
	 * @param unknown_type $index
	 */
	public function getSubFormat($index = 0) {
		
	}

	/**
	 * @param unknown_type $index
	 */
	public function getSubLanguage($index = 0) {
		
	}


	/**
	 * Fetch info about location
	 */
	private function fetch() {
		// if $this->_location should be fetched
		// $this->_fetched === false is true
		// else all datas are in $this->_fetched (array)
		if ( $this->_fetched === false ) {
			
			// fetch and decode mediainfo data here
			$fetched = array(
				'source'	=> $this->_location,
				'videos'	=> array(array('codecName' => 'h264', 'codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264)),
				'audios'	=> array(array('codecName' => 'aac', 'codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC)),
				'subs'		=> array(5 => array('format' => 'srt', 'language' => 'ita'))
			);
			
			// I use lazy init for info
			// and I insert results in cache
			$this->_fetched = $fetched;
		}
	}
	
	
}


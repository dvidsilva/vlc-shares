<?php

require_once ('X/VlcShares/Plugins/Helper/Abstract.php');
require_once ('X/VlcShares/Plugins/Helper/Mediainfo.php');
require_once ('X/VlcShares/Plugins/Helper/MPlayer.php');
require_once ('X/VlcShares/Plugins/Helper/StreaminfoInterface.php');
require_once ('X/VlcShares/Plugins/Helper/GSpot.php');

/**
 * Give info about a location
 * 
 * This is a proxy class for mediainfo, gspot or mplayer and
 * use this helper as provider for the infos.
 * It decides which provider to use in this way:
 *   - location is
 *   	url -> Mplayer (this is the only one who give info on online medias)
 *   	file -> Mediainfo (this give lot of info and in a better way)
 *   - provider is
 *   	FileSystem: location is file
 *   	Megavideo: location is url
 *   	OnlineFile: location is url
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Helper_Stream extends X_VlcShares_Plugins_Helper_Abstract implements X_VlcShares_Plugins_Helper_StreaminfoInterface {
	
	private $_location = null;
	private $_fetched = false;
	
	/**
	 * Set location source
	 * 
	 * @param $location the source
	 * @return X_VlcShares_Plugins_Helper_Stream
	 */
	function setLocation($location) {
		if ( $this->_location != $location ) {
			$this->_location = $location;
			$this->_fetched = false;
		}
		return $this;
	}
	
	/**
	 * Return all infos about the source setted 
	 * with setLocation() in an associative array
	 * The array has this format:
	 * array(
	 * 	'source'	=> $source
	 * 	'videos'	=> array from getVideosInfo()
	 * 	'audios'	=> array from getAudiosInfo()
	 * 	'subs'		=> array from getSubsInfo()
	 * )
	 * 
	 * @return array
	 */
	function getInfos() {
		$this->fetch();
		return $this->_fetched;
	}

	
	
	
	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getAudioCodecName()
	 * @param int $index
	 * @return string
	 */
	public function getAudioCodecName($index = 0) {
		$this->fetch();
		if ( $index < $this->getAudioStreamsNumber() ) {
			return $this->_fetched['audios'][$index]['codecName'];
		} else {
			throw new Exception("There is no stream $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getAudioCodecType()
	 * @param int $index
	 */
	public function getAudioCodecType($index = 0) {
		$this->fetch();
		if ( $index < $this->getAudioStreamsNumber() ) {
			return $this->_fetched['audios'][$index]['codecType'];
		} else {
			throw new Exception("There is no stream $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getAudioInfo()
	 * @return array
	 */
	public function getAudiosInfo() {
		$this->fetch();
		// @ prevent error fetched data
		return @$this->_fetched['audios'];
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getAudioStreamsNumber()
	 * @return int
	 */
	public function getAudioStreamsNumber() {
		$this->fetch();
		// @ prevent error fetched data
		return count(@$this->_fetched['audios']);
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getSubFormat()
	 * @param int $index
	 * @return string
	 */
	public function getSubFormat($index = 0) {
		$this->fetch();
		if ( $index < $this->getSubsNumber() ) {
			return $this->_fetched['subs'][$index]['format'];
		} else {
			throw new Exception("There is no sub $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getSubLanguage()
	 * @param index $index
	 * @return string
	 */
	public function getSubLanguage($index = 0) {
		$this->fetch();
		if ( $index < $this->getSubsNumber() ) {
			return $this->_fetched['subs'][$index]['language'];
		} else {
			throw new Exception("There is no sub $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getSubsInfo()
	 * @return array
	 */
	public function getSubsInfo() {
		$this->fetch();
		// @ prevent error fetched data
		return count(@$this->_fetched['subs']);
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getSubsNumber()
	 * @return int
	 */
	public function getSubsNumber() {
		$this->fetch();
		// @ prevent error fetched data
		return count(@$this->_fetched['subs']);
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getVideoCodecName()
	 * @param int $index
	 * @return string
	 */
	public function getVideoCodecName($index = 0) {
		$this->fetch();
		if ( $index < $this->getVideoStreamsNumber() ) {
			return $this->_fetched['videos'][$index]['codecName'];
		} else {
			throw new Exception("There is no stream $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getAudioCodecType()
	 * @param int $index
	 * @return int
	 */
	public function getVideoCodecType($index = 0) {
		$this->fetch();
		if ( $index < $this->getVideoStreamsNumber() ) {
			return $this->_fetched['videos'][$index]['codecType'];
		} else {
			throw new Exception("There is no stream $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getVideosInfo()
	 * @return array
	 */
	public function getVideosInfo() {
		$this->fetch();
		// @ prevent error fetched data
		return @$this->_fetched['videos'];
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getVideoStreamsNumber()
	 * @return int
	 */
	public function getVideoStreamsNumber() {
		$this->fetch();
		// @ prevent error fetched data
		return count(@$this->_fetched['videos']);
	}

	
	/**
	 * Fetch data from a provider
	 */
	private function fetch() {
		// if $this->_location should be fetched
		// $this->_fetched === false is true
		// else all datas are in $this->_fetched (array)
		if ( $this->_fetched === false ) {
			
			/**
			 * I need to check:
			 * type of url
			 * redirect fetch info to provider
			 */
			if ( X_Env::startWith($this->_location, 'http://') || X_Env::startWith($this->_location, 'https://' ) ) {
				// i should use MPlayer
			//} elseif ( false /* check for online streams */ ) {
				// i should use MPlayer
				$fetched = $this->fetchByMPlayer();
			} elseif ( true /* regex condition: W:\indows\path or W:/indows/path */ ) {
				// i should use Mediainfo
				$fetched = $this->fetchByMediainfo();
			}
			
			// I use lazy init for info
			// and I insert results in cache
			$this->_fetched = $fetched;
		}
	}

	/**
	 * Redirect fetch to Mediainfo helper
	 */
	private function fetchByMediainfo() {
		return X_VlcShares_Plugins::helpers()->mediainfo()->setLocation($this->_location)->getInfos();
	}
	
	/**
	 * Redirect fetch to MPlayer helper
	 */
	private function fetchByMPlayer() {
		return X_VlcShares_Plugins::helpers()->mplayer()->setLocation($this->_location)->getInfos();
	}
}


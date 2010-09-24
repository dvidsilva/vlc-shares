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
	
	private $formatTests = array();

	
	function __construct() {
		$this->formatTests = array(
			// audio
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC => array(array('/Format', 'AAC')),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_MP3 => array(
				array('/Format', 'MPEG Audio'),
				array('/Codec_ID_Hint', 'MP3'),
			),
			
			
			// video
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264 => array(array('/Format', 'AVC')),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_XVID => array(
				array('/Format', 'MPEG-4 Visual'),
				array('/Codec_ID', 'XVID'),
			),
			
			// unknown
			X_VlcShares_Plugins_Helper_StreaminfoInterface::AVCODEC_UNKNOWN => array()
		);
	}
	
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
			
			
			$xmlString = $this->_invoke();
			
			$dom = new Zend_Dom_Query($xmlString);
			
			$videos = array();
			$audios = array();
			$subs = array();
			
			// search for videos
			$result = $dom->queryXpath('//track[@type="Video"]');
			$found = $result->count();
			for ( $i = 1; $i <= $found; $i++) {
				$format = X_VlcShares_Plugins_Helper_StreaminfoInterface::AVCODEC_UNKNOWN;
				foreach ($this->formatTests as $key => $test) {
					$valid = true;
					foreach ($test as $subtest) {
						list($query, $value) = $subtest;
						//X_Debug::i("Query: $query / Value: $value");
						$nodeTest = $dom->queryXpath("//track[@type='Video'][$i]$query");
						if ( !$nodeTest->valid() || $nodeTest->current()->nodeValue != $value ) {
							$valid = false;
							break;
						}
					}
					if ( $valid ) {
						$format = $key;
						break;
					}
				}
				$id = $dom->queryXpath("//track[@type='Video'][$i]/ID")->current()->nodeValue;
				$videos[$id] = array('codecName' => $format, 'codecType' => $format);
			}

			// search for audios
			$result = $dom->queryXpath('//track[@type="Audio"]');
			$found = $result->count();
			for ( $i = 1; $i <= $found; $i++) {
				$format = X_VlcShares_Plugins_Helper_StreaminfoInterface::AVCODEC_UNKNOWN;
				foreach ($this->formatTests as $key => $test) {
					$valid = true;
					foreach ($test as $subtest) {
						list($query, $value) = $subtest;
						$nodeTest = $dom->queryXpath("//track[@type='Audio'][$i]$query");
						if ( !$nodeTest->valid() || $nodeTest->current()->nodeValue != $value ) {
							$valid = false;
							break;
						}
					}
					if ( $valid ) {
						$format = $key;
						break;
					}
				}
				$id = $dom->queryXpath("//track[@type='Audio'][$i]/ID")->current()->nodeValue;
				$audios[$id] = array('codecName' => $format, 'codecType' => $format);
			}
			
			
			//X_Debug::i(var_export($videos, true));
			//X_Debug::i(var_export($audios, true));
			
			// fetch and decode mediainfo data here
			$fetched = array(
				'source'	=> $this->_location,
				//'videos'	=> array(array('codecName' => 'h264', 'codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264)),
				//'audios'	=> array(array('codecName' => 'aac', 'codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC)),
				'videos'	=> $videos, // should indentify correctly
				'audios'	=> $audios, // should identify correctly
				'subs'		=> array(5 => array('format' => 'srt', 'language' => 'ita'))
			);
			
			// I use lazy init for info
			// and I insert results in cache
			$this->_fetched = $fetched;
		}
	}
	
	private function _invoke() {
		$source = $this->_location;
		$str = X_Env::execute("mediainfo --Output=XML \"$source\"", X_Env::EXECUTE_OUT_IMPLODED, X_Env::EXECUTE_PS_WAIT );
		return trim($str);
	}
	
}


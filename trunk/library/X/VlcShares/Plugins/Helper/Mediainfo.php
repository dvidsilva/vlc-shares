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

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options = null;
	
	function __construct(Zend_Config $options) {
		
		$this->options = $options;
		$this->formatTests = array(
			// audio
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC => array(array('/Format', 'AAC')),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AC3 => array(array('/Format', 'AC-3')),
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
	 * Show if helper is enabled and ready to get infos
	 * @return boolean
	 */
	function isEnabled() {
		return ($this->options->get('enabled', false) && file_exists($this->options->get('path', false))); 
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
	public function getAudioCodecName($index = -1) {
		$this->fetch();
		if ( $index == -1 ) {
			reset($this->_fetched['audios']);
			$index = key($this->_fetched['audios']);
		}
		if ( array_key_exists($index, $this->_fetched['audios']) ) {
			return $this->_fetched['audios'][$index]['codecName'];
		} else {
			throw new Exception("There is no stream $index in source {$this->_location}");
		}
	}

	/**
	 * @see X_VlcShares_Plugins_Helper_StreaminfoInterface::getAudioCodecType()
	 * @param int $index
	 */
	public function getAudioCodecType($index = -1) {
		$this->fetch();
		if ( $index == -1 ) {
			reset($this->_fetched['audios']);
			$index = key($this->_fetched['audios']);
		}
		if ( array_key_exists($index, $this->_fetched['audios']) ) {
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
	public function getSubFormat($index = -1) {
		$this->fetch();
		if ( $index == -1 ) {
			reset($this->_fetched['subs']);
			$index = key($this->_fetched['subs']);
		}
		if ( array_key_exists($index, $this->_fetched['subs']) ) {
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
	public function getSubLanguage($index = -1) {
		$this->fetch();
		if ( $index == -1 ) {
			reset($this->_fetched['subs']);
			$index = key($this->_fetched['subs']);
		}
		if ( array_key_exists($index, $this->_fetched['subs']) ) {
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
		return @$this->_fetched['subs'];
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
	public function getVideoCodecName($index = -1) {
		$this->fetch();
		if ( $index == -1 ) {
			reset($this->_fetched['videos']);
			$index = key($this->_fetched['videos']);
		}
		if ( array_key_exists($index, $this->_fetched['videos']) ) {
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
	public function getVideoCodecType($index = -1) {
		$this->fetch();
		if ( $index == -1 ) {
			reset($this->_fetched['videos']);
			$index = key($this->_fetched['videos']);
		}
		if ( array_key_exists($index, $this->_fetched['videos']) ) {
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
	 * Fetch info about location
	 */
	private function fetch() {
		// if $this->_location should be fetched
		// $this->_fetched === false is true
		// else all datas are in $this->_fetched (array)
		if ( $this->_fetched === false ) {
			
			if ( !$this->options->enabled || !file_exists($this->options->path) ) {
				X_Debug::e('Helper disabled or wrong path');
				$this->_fetched = array(
					'source'	=> $this->_location,
					'videos'	=> array(),
					'audios'	=> array(),
					'subs'		=> array()
				);
				return;
			} else {		
				$xmlString = $this->_invoke();
			}
			
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
				$id = $dom->queryXpath("//track[@type='Video'][$i]/ID");
				if ( $id->valid() ) {
					$id = $id->current()->nodeValue;
					$videos[$id] = array('codecName' => $format, 'codecType' => $format);
				} else {
					$videos[] = array('codecName' => $format, 'codecType' => $format);
				}
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
				$id = $dom->queryXpath("//track[@type='Audio'][$i]/ID");
				if ( $id->valid() ) {
					$id = $id->current()->nodeValue;
					$audios[$id] = array('codecName' => $format, 'codecType' => $format);
				} else {
					$audios[] = array('codecName' => $format, 'codecType' => $format);
				}
				
			}
			

			// search for audios
			$result = $dom->queryXpath('//track[@type="Text"]');
			$found = $result->count();
			for ( $i = 1; $i <= $found; $i++) {
				$language = $dom->queryXpath("//track[@type='Text'][$i]/Language")->current()->nodeValue;
				$format = $dom->queryXpath("//track[@type='Text'][$i]/Format")->current()->nodeValue;
				$id = $dom->queryXpath("//track[@type='Text'][$i]/ID");
				if ( $id->valid() ) {
					$id = $id->current()->nodeValue;
					$subs[] = array('format' => $format, 'language' => $language, 'id' => $id);
				} else {
					$subs[] = array('format' => $format, 'language' => $language);
				}
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
				'subs'		=> $subs
			);
			
			// I use lazy init for info
			// and I insert results in cache
			$this->_fetched = $fetched;
		}
	}
	
	private function _invoke() {
		$source = $this->_location;
		
		$mediainfo = "\"{$this->options->path}\"";
		
		$str = X_Env::execute("$mediainfo --Output=XML \"$source\"", X_Env::EXECUTE_OUT_IMPLODED, X_Env::EXECUTE_PS_WAIT );
		return trim($str);
	}
	
}


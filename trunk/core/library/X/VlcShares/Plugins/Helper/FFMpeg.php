<?php

require_once ('X/VlcShares/Plugins/Helper/Abstract.php');
require_once ('X/VlcShares/Plugins/Helper/StreaminfoInterface.php');

class X_VlcShares_Plugins_Helper_FFMpeg extends X_VlcShares_Plugins_Helper_Abstract implements X_VlcShares_Plugins_Helper_StreaminfoInterface {
	
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
		
		//X_Debug::i("Helper options: ".var_export($options->toArray(), true));
		
		$this->formatTests = array(
			// audio
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC => array('aac'),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AC3 => array('ac3'),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_MP3 => array('mp3'),
			
			
			// video
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_FLV => array('flv'),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264 => array('h264'),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_XVID => array('mpeg4'),
			
			// unknown
			X_VlcShares_Plugins_Helper_StreaminfoInterface::AVCODEC_UNKNOWN => array('')
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
				X_Debug::e("Helper disabled ({$this->options->enabled}) or wrong path ({$this->options->path})");
				$ffmpegOutput = array();
			} else {		
				$ffmpegOutput = $this->_invoke();
			}
			
			//$dom = new Zend_Dom_Query($xmlString);

			$fetched = array(
				'source'	=> $this->_location,
				//'videos'	=> array(array('codecName' => 'h264', 'codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264)),
				//'audios'	=> array(array('codecName' => 'aac', 'codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC)),
				'videos'	=> array(), // should indentify correctly
				'audios'	=> array(), // should identify correctly
				'subs'		=> array()
			);
			
			//X_Debug::i(var_export($ffmpegOutput, true));
			
			foreach ($ffmpegOutput as $line) {
				$line = trim($line);
				if ( $line == '' ) continue; // jump away from empty line
				
				
				// we are looking for line like this:
				// Stream #0.0(jpn): Video: h264, yuv420p, 1280x720, PAR 1:1 DAR 16:9, 23.98 tbr, 1k tbn, 47.95 tbc
			    // Stream #0.1(jpn): Audio: aac, 48000 Hz, stereo, s16
			    // Stream #0.2(ita): Subtitle: 0x0000
			    // Stream #0.3: Attachment: 0x0000   <--- MKV Menu
				
				// OR DIFFERENT VERSION:
				
				//Stream #0:0: Video: h264 (High) (H264 / 0x34363248), yuv420p, 640x480 [SAR 1:1 DAR 4:3], 23.98 fps, 23.98 tbr, 1k tbn, 47.95 tbc (default)
				//Stream #0:1(eng): Audio: vorbis, 48000 Hz, stereo, s16 (default)
				//Stream #0:2(jpn): Audio: vorbis, 48000 Hz, mono, s16
				//Stream #0:3(eng): Subtitle: ssa (default)
				
				//if ( !X_Env::startWith($line, 'Stream #0') ) continue;
				
				$matches = array();
				$pattern = '/Stream #(?P<mainid>\d+)(.|:)(?P<subid>\d+)(?P<lang>(\(\w+\))?): (?P<type>\w+): (?P<codec>[^,\s]+)(?P<extra>.*)/';
				if ( !preg_match($pattern, $line, $matches) ) {
					continue;
				}
				
				X_Debug::i("Checking line: $line");
				
				$language = @$matches['lang'];
				$streamID = $matches['subid'];
				$streamType = $matches['type'];
				$streamFormat = $matches['codec'];
				$streamMore = $matches['extra'];
				
				// it's the line we are looking for
				
				// time to split
				//list(, $streamID, $streamType, $streamFormat, $streamMore ) = explode(' ', $line, 5);
				
				/*
				X_Debug::i("StreamID (raw): $streamID");
				X_Debug::i("StreamType (raw): $streamType");
				X_Debug::i("StreamFormat (raw): $streamFormat");
				X_Debug::i("StreamMore (raw): $streamMore");
				*/
				
				// in 0 -> Stream
				// in 1 -> #0.StreamID(language):    <--- language is present only in mkv files. For avi, no (language)
				// in 2 -> StreamType:  <---- Video|Audio|Subtitle|Attachment
				// in 3 -> StreamFormat, blablabla   <--- for audio and video
				//    OR
				// in 3 -> StreamFormat   <---- for subtitle and attachment
				
				switch ( $streamType ) {
					case 'Video': $streamType = 'videos'; break;
					case 'Audio': $streamType = 'audios'; break;
					case 'Subtitle': $streamType = 'subs'; break;
					default: $streamType = false;
				}
				
				if ( !$streamType ) continue; // check for Annotation or unknown type of stream
				
				// time to get the real streamID
				
				//
				//@list($streamID, $language) = explode('(', trim($streamID, '):'), 2);
				// in $streamID there is : #0.1
				// discard the first piece
				//list( , $streamID) = explode('.', ltrim($streamID,'#'), 2);
				
				$infoStream = array();
				if ( $streamType == 'subs' ) {
					// i don't need format decoding for subs
					$infoStream['format'] = trim($streamFormat);
					$infoStream['language'] = $language;
				} else {
					$infoStream = array('codecType' => X_VlcShares_Plugins_Helper_StreaminfoInterface::AVCODEC_UNKNOWN,
					 'codecName' => X_VlcShares_Plugins_Helper_StreaminfoInterface::AVCODEC_UNKNOWN);
					foreach ($this->formatTests as $key => $test) {
						$valid = false;
						if ( $test[0] == $streamFormat ) {
							$valid = true;
							if ( count($test) > 1 && !X_Env::startWith(trim($streamMore), $test[1] ) ) {
								$valid = false;
							}
						}
						if ( $valid ) {
							$infoStream = array('codecType' => $key, 'codecName' => $key);
							break;
						}
					}
				}
				$infoStream['ID'] = $streamID;
				// language if available
				if ( $language ) $infoStream['language'] = $language;
				//$fetched[$streamType][$streamID] = $infoStream;
				// no index as key for use with vlc --sub-track 
				
				X_Debug::i("Stream type {{$streamType}} found: ".print_r($infoStream, true));
				
				$fetched[$streamType][] = $infoStream;
			}
			
			//X_Debug::i(var_export($fetched, true));
			
			// I use lazy init for info
			// and I insert results in cache
			$this->_fetched = $fetched;
		}
	}
	
	private function _invoke() {
		$source = $this->_location;
		
		$osTweak = X_Env::isWindows() ? '2>&1' : '2>&1';
		
		$ffmpeg = "\"{$this->options->path}\"";
		
		$str = X_Env::execute("$ffmpeg -i \"$source\" $osTweak", X_Env::EXECUTE_OUT_ARRAY, X_Env::EXECUTE_PS_WAIT );
		return $str;
	}
		
}

	
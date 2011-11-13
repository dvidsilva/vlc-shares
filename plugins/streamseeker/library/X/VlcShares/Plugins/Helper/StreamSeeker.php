<?php 


class X_VlcShares_Plugins_Helper_StreamSeeker extends X_VlcShares_Plugins_Helper_Abstract {
	
	/**
	 * @var Zend_Config
	 */
	private $options = null;
	
	private $supportedHosters = array(
		// class => seek rewrite
		//'X_VlcShares_Plugins_Helper_Hoster_Megavideo' => '{%URL_T_SLASH%}/{%SEEK%}',
		//'X_VlcShares_Plugins_Helper_Hoster_Megaupload' => '{%URL_T_SLASH%}/{%SEEK%}',
		'X_VlcShares_Plugins_Helper_Hoster_RealDebridMegavideo' => '{%URL%}?start={%SEEK%}',
		'X_VlcShares_Plugins_Helper_Hoster_RealDebridMegaupload' => '{%URL%}?start={%SEEK%}',
		'X_VlcShares_Plugins_Helper_Hoster_RealDebridMegaporn' => '{%URL%}?start={%SEEK%}',
		'X_VlcShares_Plugins_Helper_Hoster_RealDebridVideoBB' => '{%URL%}?start={%SEEK%}',
		'X_VlcShares_Plugins_Helper_Hoster_RealDebridRapidShare' => '{%URL%}?start={%SEEK%}',
		'X_VlcShares_Plugins_Helper_Hoster_RealDebrid4Shared' => '{%URL%}?start={%SEEK%}',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
	}
	
	public function isSeekableHoster(X_VlcShares_Plugins_Helper_HostInterface $hoster) {
		$hosterClass = get_class($hoster);
		return array_key_exists($hosterClass, $this->supportedHosters);
	}
	
	public function getSeekedUrl($cleanUrl, $seekValue, X_VlcShares_Plugins_Helper_HostInterface $hoster) {
		
		$hosterClass = get_class($hoster);
		
		$tSlashUrl = rtrim($cleanUrl, '/');
		$tQueryUrl = rtrim(substr($cleanUrl, 0, strrpos($cleanUrl, '?') ), '?');
		$seekedUrl = str_replace(array(
			'{%URL%}',
			'{%SEEK%}',
			'{%URL_T_SLASH%}',
		), array(
			$cleanUrl,
			$seekValue,
			$tQueryUrl,
			$tSlashUrl
		), $this->supportedHosters[$hosterClass]);
		
		//$testUrl = "http://localhost/vlc-shares/testjoin.php?seek=$seekValue&url=".urlencode($cleanUrl);
		//return $testUrl;
		return $seekedUrl;
		
	}
	
	public function getPositions($location, $sourceFile) {
		
		$positions = array();
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers('cache');
			$positions = $cacheHelper->retrieveItem("streamseeker::{$location}");
			if ( $positions ) {
				$positions = @unserialize($positions);
			}
			X_Debug::i("Using positions values stored in cache about {$location}");
			// return stored values
			return $positions;
		} catch (Exception $e) {
			// no cache plugin or no positions cached
			X_Debug::i("No FLV info in cache about {$location}");
		}
			
		// dump 9kb of the file to analyze it
		$tmpFile = tempnam(sys_get_temp_dir(), 'fia');
		$sampleSize = intval($this->options->get('samplesize', 100)) * 1000;
		
		X_Debug::i("Downloading {$sampleSize} bytes in {$tmpFile} from {$sourceFile}");
		
		$src = fopen($sourceFile, 'r');
		$dest = fopen($tmpFile, 'w');
		$copied = stream_copy_to_stream($src, $dest, $sampleSize);
		
		fclose($src);
		fclose($dest);
		
		X_Debug::i("{$copied} bytes downloaded");
		
		try {
			$flvinfo = new X_FLVInfo();
			$fileInfo = $flvinfo->getInfo($tmpFile, true, true);
			
			if ( $fileInfo->signature ) {
			
				$keyframes = @$fileInfo->rawMeta[1]['keyframes'];
				$times = $keyframes->times;
				$filepositions = $keyframes->filepositions;
				
				$lastAdd = 0;
				//$firstKey = 0;
				$minDelta = intval($this->options->get('mindelta', 5)) * 60; // mindelta is in minutes
				
				// time to parse and filter the response
				// filter using a minDelta function
				foreach ( $times as $key => $seconds ) {
					/*
					if ( $key == 0 ) {
						$firstKey = $filepositions[$key];
						continue;
					}*/
					if ( ($seconds - $lastAdd) > $minDelta ) { 
						// new step
						//$positions["{$filepositions[$key]},{$firstKey}"] = X_Env::formatTime(intval($seconds));
						$positions[$filepositions[$key]] = X_Env::formatTime(intval($seconds));
						$lastAdd = intval($seconds);
					}
				}
				
				X_Debug::i("Valid position found in flv file: ".count($positions));
				
				// parse done, store
				try {
					/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
					$cacheHelper = X_VlcShares_Plugins::helpers('cache');
					$cacheHelper->storeItem("streamseeker::$location", serialize($positions), $this->options->get('cachevalidity', 10));
				} catch (Exception $e) {
					X_Debug::e("Cache is disabled. This is really bad for streamseeker");
				}
				
			} else {
				X_Debug::w("Wrong signature. Can't analyze");
			}
			
		} catch (Exception $e) {
			X_Debug::e("FLVInfo throws an error: {$e->getMessage()}");
		}
		
		return $positions;
	}
	
}

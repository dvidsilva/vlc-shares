<?php 


class X_VlcShares_Plugins_Helper_Hoster_Youtube implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'youtube';
	const PATTERN = '/http\:\/\/((www\.)?)youtube\.com\/watch\?v\=(?P<ID>[A-Za-z0-9._%-]+)/';
	
	/**
	 * give the hoster id
	 * @return string
	 */
	function getId() {
		return self::ID;
	}
	/**
	 * get the hoster pattern for regex match
	 * @return string
	 */
	function getPattern() {
		return self::PATTERN;
	}
	/**
	 * get the resource ID for the hoster
	 * from an $url
	 * @param string $url the hoster page
	 * @return string the resource id
	 */
	function getResourceId($url) {
		$matches = array();
		if ( preg_match(self::PATTERN, $url, $matches ) ) {
			if ( $matches['ID'] != '' ) {
				return $matches['ID'];
			}
			X_Debug::e("No id found in {{$url}}", self::E_ID_NOTFOUND);
			throw new Exception("No id found in {{$url}}");
		} else {
			X_Debug::e("Regex failed");
			throw new Exception("Regex failed", self::E_URL_INVALID);
		}
	}
	/**
	 * get a playable resource url
	 * from an $url (or a resource id if $isId = true)
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return string a playable url
	 */
	function getPlayable($url, $isId = true) {
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}
		// $url is an id now for sure
		/* @var $youtubeHelper X_VlcShares_Plugins_Helper_Youtube */
		$youtubeHelper = X_VlcShares_Plugins::helpers()->helper('youtube');
		/* @var $youtubePlugin X_VlcShares_Plugins_Youtube */
		$youtubePlugin = X_VlcShares_Plugins::broker()->getPlugins('youtube');
		
		X_Debug::i("Youtube ID: $url");

		// THIS CODE HAVE TO BE MOVED IN YOUTUBE HELPER
		// FIXME
		$formats = $youtubeHelper->getFormatsNOAPI($url);
		$returned = null;
		$qualityPriority = explode('|', $youtubePlugin->config('quality.priority', '5|34|18|35'));
		foreach ($qualityPriority as $quality) {
			if ( array_key_exists($quality, $formats)) {
				$returned = $formats[$quality];
				X_Debug::i('Video format selected: '.$quality);
				break;
			}
		}
		if ( $returned === null ) {
			// for valid video id but video with restrictions
			// alternatives formats can't be fetched by youtube page.
			// i have to fallback to standard api url
			$apiVideo = $youtubeHelper->getVideo($url);
			
			foreach ($apiVideo->mediaGroup->content as $content) {
				if ($content->type === "video/3gpp") {
					$returned = $content->url;
					X_Debug::w('Content restricted video, fallback to api url:'.$returned);
					break;
				}
			}

			if ( $returned === null ) {
				$returned = false;
			}
		}					

		if ( $returned !== false && $returned !== null ) {
			// valid return
			return $returned;
		}
		
		throw new Exception("Invalid video", self::E_ID_INVALID);
	}
	
	/**
	 * get an array with standard information about the playable
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return array format:
	 * 		array(
	 * 			'title' => TITLE
	 * 			'description' => DESCRIPTION
	 * 			'length' => LENGTH
	 * 			...
	 * 		)
	 */
	function getPlayableInfos($url, $isId = true) {
		
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}

		// $url is an id now for sure
		/* @var $youtubeHelper X_VlcShares_Plugins_Helper_Youtube */
		$youtubeHelper = X_VlcShares_Plugins::helpers()->helper('youtube');
		
		try {
		
			$videoEntry = $youtubeHelper->getVideo($url);
			
			$thumb = $videoEntry->getVideoThumbnails();
			$thumb = @$thumb[0]['url'];
			$thumb = str_replace('default', '0', $thumb);
			
			// use cached values
			$infos = array(
				'title' => $videoEntry->getVideoTitle(),
				'description' => $videoEntry->getVideoDescription(),
				'length' => X_Env::formatTime($videoEntry->getVideoDuration()),
				'thumbnail' => $thumb 
			);
			
			return $infos;
			
		} catch (Exception $e) {
			throw new Exception("Invalid video", self::E_ID_INVALID);
		}
	}

	function getHosterUrl($playableId) {
		return "http://www.youtube.com/watch?=$playableId";
	}
	
	
}

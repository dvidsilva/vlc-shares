<?php 

class X_VlcShares_Plugins_Helper_Hoster_VideoBB implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'videobb';
	const PATTERN = '/http\:\/\/((www\.)?)videobb\.com\/(video\/|watch_video.php\?v\=)(?P<ID>[A-Za-z0-9]+)/';
	
	private $info_cache = array();
	
	
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
		$infos = $this->getPlayableInfos($url, $isId);
		return $infos['url'];
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
		
		// use cached values
		if ( array_key_exists($url, $this->info_cache) ) {
			return $this->info_cache[$url];
		}
		
		// use the api
		$http = new Zend_Http_Client("http://www.videobb.com/player_control/settings.php?v=" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." videobb/".X_VlcShares_Plugins_VideoBB::VERSION
				)
			)
		);
		
		$datas = $http->request()->getBody();

		$json = Zend_Json::decode($datas);
		
		if ( @$json['settings']['messages']['display']['text'] == 'The video you have requested is not available' ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$infos = array(
			'title' => @$json['settings']['video_details']['video']['title'],
			'description' => @$json['settings']['video_details']['video']['description'],
			'length' => 0,
			'thumbnail' => @$json['settings']['config']['thumbnail'],
			'url' => @base64_decode(@$json['settings']['config']['token1'])
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.videobb.com/video/$playableId";
	}
	
}

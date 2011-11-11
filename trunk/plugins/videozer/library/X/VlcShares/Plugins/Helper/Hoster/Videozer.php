<?php 

class X_VlcShares_Plugins_Helper_Hoster_Videozer implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'videozer';
	const PATTERN = '/http:\/\/(www\.)?videozer\.com\/(video|embed)\/(?P<ID>[A-Za-z0-9]+)/i';
	
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
		if ( $infos['rawinfo']['cfg']['msg'][0] != "you still have quota left" ) {
			throw new Exception("No more quota left", 99); // temp substitution for self::E_QUOTA_NOMORE
		}
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
		$http = new Zend_Http_Client("http://www.videozer.com/player_control/settings.php?v=" . $url . '&fv=v1.1.12',
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." videozer/".X_VlcShares_Plugins_Videozer::VERSION
				)
			)
		);
		
		$datas = $http->request()->getBody();

		if ( preg_match("/(\"The page you have requested cannot be found|>The web page you were attempting to view may not exist or may have moved|>Please try to check the web address for typos)/i", $datas)) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$json = Zend_Json::decode($datas);
		
		$infos = array(
			'title' => @$json["cfg"]['info']['video']['title'],
			'description' => @$json["cfg"]['info']['video']['description'],
			'length' => 0,
			'thumbnail' => @$json["cfg"]['environment']['thumbnail'],
			'url' => @html_entity_decode(@base64_decode(@$json['cfg']["quality"][0]["u"])),
			'rawinfo' => $json
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.videozer.com/video/$playableId";
	}
	
}

<?php 

class X_VlcShares_Plugins_Helper_Hoster_DailyMotion implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'dailymotion';
	const PATTERN = '/http\:\/\/(www\.)?dailymotion\.(virgilio\.)?(com|it)\/video\/(?P<ID>[a-z0-9]+)(_.*)?/';
	
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
		
		$http = new Zend_Http_Client("http://www.dailymotion.com/video/" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." dailymotion/".X_VlcShares_Plugins_DailyMotion::VERSION
				)
			)
		);
		$http->setCookieJar(true);
		$http->getCookieJar()->addCookie(new Zend_Http_Cookie('family_filter', 'off', 'www.dailymotion.com'));
		
		$datas = $http->request()->getBody();

		if ( preg_match('/<title>(.*)404(.*)<\/title>/', $datas)  ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}

		$matches = array();
		if ( !preg_match('/\.addVariable\(\"sequence\",  \"(?P<sequence>.*?)\"/', $datas, $matches)  ) {
			throw new Exception("Invalid ID {{$url}}, sequence not found", self::E_ID_INVALID);
		}
		$sequence = urldecode($matches['sequence']);
		
		$matches = array();
		if ( !preg_match('/videotitle\=(?P<title>[^&]+)&/', $sequence, $matches)  ) {
			$title = "";
		}
		$title = urldecode($matches['title']);

		$matches = array();
		if ( !preg_match('/\"videoDescription\"\:\"(?P<description>[^\"]*)\"/', $sequence, $matches)  ) {
			$description = '';
		}
		$description = urldecode($matches['description']);

		
		$matches = array();
		if ( !preg_match('/\"duration\"\:(?P<length>.*)\,/', $sequence, $matches)  ) {
			$length = '';
		}
		$length = $matches['length'];
		
		
		$thumbnail = "http://www.dailymotion.com/thumbnail/320x240/video/$url";
		
		$matches = array();
		if ( !preg_match('/\"sdURL\"\:\"(?P<video>[^\"]+)\"/', $sequence, $matches)  ) {
			$video = '';
		}
		$video = stripslashes($matches['video']);
		
		$infos = array(
			'title' => $title,
			'description' => $description,
			'length' => $length,
			'thumbnail' => $thumbnail,
			'url' => $video
		);		
		
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.videobb.com/video/$playableId";
	}
	
}

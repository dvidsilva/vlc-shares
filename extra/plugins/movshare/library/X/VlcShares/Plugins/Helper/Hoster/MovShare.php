<?php 

class X_VlcShares_Plugins_Helper_Hoster_MovShare implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'movshare';
	const PATTERN = '/http\:\/\/(www\.)?movshare\.net\/video\/(?P<ID>[a-z0-9]+)(#)?/';
	
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
		$infos = $this->getPlayableInfos($url, true);
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
		$http = new Zend_Http_Client("http://www.movshare.net/video/$url",
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." movshare/".X_VlcShares_Plugins_MovShare::VERSION
				)
			)
		);
		// this allow to store the cookie for multiple requests
		$http->setCookieJar(true);
		
		$datas = $http->request()->getBody();
		
		if ( strpos($datas, 'We need you to prove you\'re human') !== false ) {
			// I have to do the request, again
			$datas = $http->request()->getBody();
			if ( strpos($datas, 'We need you to prove you\'re human') !== false ) {
				throw new Exception("Hoster requires interaction");
			}
		}
		
		// now datas should contains the html
		// time to grab some informations 
		
		if ( strpos($datas, 'This file no longer exists on our servers') !== false ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$matches = array();
		if ( !preg_match('/Title\: <\/strong>(?P<title>[^\<]+)</', $datas, $matches)  ) {
			$title = "";
		}
		$title = $matches['title'];

		$matches = array();
		if ( !preg_match('/Description\: <\/strong>(?P<description>[^\<]+)</', $datas, $matches)  ) {
			$description = '';
		}
		$description = $matches['description'];
		
		$length = 0;
		$thumbnail = '';
		
		$matches = array();
		if ( !preg_match('/param name\=\"src\" value\=\"(?P<video>[^\"]+)\"/', $datas, $matches)  ) {
			$video = '';
		}
		$video = $matches['video'];
		
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
		return "http://www.movshare.net/video/$playableId";
	}
	
}

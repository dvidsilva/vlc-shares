<?php 

class X_VlcShares_Plugins_Helper_Hoster_4Shared implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = '4shared';
	const PATTERN = '/http\:\/\/((www\.)?)4shared\.com\/video\/(?P<ID>[A-Za-z0-9]+)\/(.*)\.htm/';
	
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
		$http = new Zend_Http_Client("http://www.4shared.com/video/" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." 4shared/".X_VlcShares_Plugins_4Shared::VERSION
				)
			)
		);
		
		$xml = $http->request()->getBody();

		if ( preg_match('/<!--\/\/ ref\:null-->/', $xml)  ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$matches = array();
		if ( !preg_match('/<h1 id\=\"fileNameText\">(?P<title>[^\<]+)<\/h1>/', $xml, $matches)  ) {
			$title = X_Env::_('p_4shared_title_not_setted');
		}
		$title = $matches['title'];

		$matches = array();
		if ( !preg_match('/<h2 id\=\"fileDescriptionText\">(?P<description>[^\<]+)<\/h2>/', $xml, $matches)  ) {
			$description = '';
		}
		$description = $matches['description'];
		
		/*
		$matches = array();
		if ( !preg_match('/length\=\"(?P<length>[^\"]+)\"/', $xml, $matches)  ) {
			$length = '';
		}
		$length = $matches['length'];
		*/
		$length = 0;
		
		$matches = array();
		if ( !preg_match('/image: \"(?P<thumbnail>[^\"]+)\",/', $xml, $matches)  ) {
			$thumbnail = '';
		}
		$thumbnail = $matches['thumbnail'];
		
		$matches = array();
		if ( !preg_match('/file: \"(?P<video>[^\"]+)\"/', $xml, $matches)  ) {
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
		return "http://www.4shared.com/video/$playableId/.htm";
	}
	
	
}

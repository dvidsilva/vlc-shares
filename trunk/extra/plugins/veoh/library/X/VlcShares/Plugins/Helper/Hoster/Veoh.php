<?php 

class X_VlcShares_Plugins_Helper_Hoster_Veoh implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'veoh';
	const PATTERN = '/http\:\/\/((www\.)?)veoh\.com\/(watch|videos)\/(?P<ID>[A-Za-z0-9]+)/';
	
	
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
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}
		// TODO find alternative
		return X_Env::routeLink(
			'veoh', 'video', array(
				'id' => $url
			)
		);
		
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
		$http = new Zend_Http_Client("http://www.veoh.com/rest/v2/execute.xml?apiKey=" . base64_decode(X_VlcShares_Plugins_Veoh::APIKEY) . "&method=veoh.video.findByPermalink&permalink=" . $url . "&",
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." veoh/".X_VlcShares_Plugins_Veoh::VERSION
				)
			)
		);
		
		$xml = $http->request()->getBody();

		if ( preg_match('/stat\=\"fail"/', $xml)  ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$matches = array();
		if ( !preg_match('/title\=\"(?P<title>[^\"]+)\"/', $xml, $matches)  ) {
			$title = X_Env::_('p_veoh_title_not_setted');
		}
		$title = $matches['title'];

		$matches = array();
		if ( !preg_match('/description\=\"(?P<description>[^\"]+)\"/', $xml, $matches)  ) {
			$description = '';
		}
		$description = $matches['description'];
		
		$matches = array();
		if ( !preg_match('/length\=\"(?P<length>[^\"]+)\"/', $xml, $matches)  ) {
			$length = '';
		}
		$length = $matches['length'];

		$matches = array();
		if ( !preg_match('/fullHighResImagePath\=\"(?P<thumbnail>[^\"]+)\"/', $xml, $matches)  ) {
			$thumbnail = '';
		}
		$thumbnail = $matches['thumbnail'];
		
		$infos = array(
			'title' => $title,
			'description' => $description,
			'length' => $length,
			'thumbnail' => $thumbnail
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.veoh.com/watch/$playableId";
	}
	
	
}

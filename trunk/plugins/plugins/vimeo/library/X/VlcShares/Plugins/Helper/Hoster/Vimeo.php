<?php 

class X_VlcShares_Plugins_Helper_Hoster_Vimeo implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'vimeo';
	const PATTERN = '/http\:\/\/((www\.)?)vimeo\.com\/(?P<ID>[0-9]+)/';
	
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
		$infos = $this->getPlayableInfos($url, true);
		return "http://vimeo.com/moogaloop/play/clip:{$url}/{$infos['request_signature']}/{$infos['request_signature_expires']}";
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
		$http = new Zend_Http_Client("http://www.vimeo.com/moogaloop/load/clip:$url/local?param_force_embed=0&param_clip_id=$url&param_show_portrait=0&param_multimoog=&param_server=vimeo.com&param_show_title=0&param_autoplay=0&param_show_byline=0&param_color=00ADEF&param_fullscreen=1&param_md5=0&param_context_id=&context_id=null",
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." vimeo/".X_VlcShares_Plugins_Vimeo::VERSION
				)
			)
		);
		
		$datas = $http->request()->getBody();
		
		$xml = new SimpleXMLElement($datas);
		
		if ( @$xml['error'] ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$infos = array(
			'title' => (string) @$xml->video->caption,
			'description' => '',
			'length' => X_Env::formatTime((string) @$xml->video->duration),
			'thumbnail' => (string) @$xml->video->thumbnail,
			'request_signature' => (string) @$xml->request_signature,
			'request_signature_expires' => (string) @$xml->request_signature_expires,
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.vimeo.com/$playableId";
	}
	
}

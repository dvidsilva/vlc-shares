<?php 

class X_VlcShares_Plugins_Helper_Hoster_2Shared implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = '2shared';
	const PATTERN = '/http\:\/\/((www\.)?)2shared\.com\/.*?\/(?P<ID>.*?)\/.*/';
	
	private $info_cache = array();
	
	private $hide = false;
	
	public function __construct($hideUserAgent = false) {
		$this->hide = $hideUserAgent;
	}
	
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
		$http = new Zend_Http_Client($this->getHosterUrl($url),
			array(
				'headers' => array(
					'User-Agent' => $this->hide ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' 2shared/'.X_VlcShares_Plugins_YouPorn::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
				)
			)
		);
		$datas = $http->request()->getBody();
		
		$match = array();
		if ( preg_match('/function .*?\(\)\{.*?window\.location \=\'(?P<URL>.*?)\';/si', $datas, $match) < 1) {
			throw new Exception("Download link not found for ID {{$url}}", self::E_ID_INVALID);
		}
		$downloadURL = $match['URL'];

		
		$match = array();
		if ( preg_match('/<div class="dname hidelong".*?\/>(?P<title>.*?)<\/div>/si', $datas, $match) ) {
			$title = trim($match['title']);
		} else {
			$title = '';
		}
		
		/*$match = array();
		if ( preg_match('/<h2 class="body" >(?P<description>.*?)<\/h2>/si', $datas, $match) ) {
			$description = $match['description'];
		} else {*/
			$description = '';
		//}
		
		$infos = array(
			'title' => $title,
			'description' => $description,
			'length' => 0,
			'url' => $downloadURL,
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.2shared.com/video/$playableId";
	}
	
}

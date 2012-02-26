<?php 

class X_VlcShares_Plugins_Helper_Hoster_Veetle implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'veetle';
	// http://www.veetle.com/index.php/channel/view#4cd067497c1bd
	const PATTERN = '/http\:\/\/((www\.)?)veetle\.com\/index\.php\/([^\#]*)#(?P<ID>[A-Za-z0-9]+)/';
	
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
		//$infos = $this->getPlayableInfos($url, $isId);
		//return $infos['url'];
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}
		
		if ( isset($this->info_cache[$url]) && isset($this->info_cache[$url]['url']) ) {
			return $this->info_cache[$url]['url'];
		}
		
		$http = new Zend_Http_Client("http://veetle.com/index.php/channel/ajaxStreamLocation/{$url}/flash",
				array(
						'headers' => array(
								'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." veetle/".X_VlcShares_Plugins_Veetle::VERSION
						)
				)
		);
		
		$result = false;
		
		$json = $http->request()->getBody();
		
		$json = @Zend_Json::decode($json);
		
		if ( @!$json['success'] ) {
			X_Debug::e("Can't find stream info about channel {{$url}}");
			//throw new Exception("Invalid ID {{$url}}. Can't find channel info", self::E_ID_INVALID);
		} else {
			$result = $json['payload'];
		}
		
		$this->info_cache[$url]['url'] = $result;
		
		return $result;
		
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
		$http = new Zend_Http_Client("http://www.veetle.com/index.php/channel/ajaxInfo/" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." veetle/".X_VlcShares_Plugins_Veetle::VERSION
				)
			)
		);
		
		$datas = $http->request()->getBody();

		$json = Zend_Json::decode($datas);
		
		if ( @$json['success'] != 1 ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$json = Zend_Json::decode($json['payload']);
		
		if ( !isset($json['flashEnabled']) || !$json['flashEnabled'] ) {
			X_Debug::e("Selected channel is not flash enabled {{$url}}");
			throw new Exception("Invalid ID {{$url}}. Channel is not flash enabled", self::E_ID_INVALID);
		}
		
		$infos = array(
			'title' => @$json['title'],
			'description' => @$json['description'],
			'length' => 0,
			'thumbnail' => @$json['logo']['lg'],
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.veetle.com/index.php/channel/view#$playableId";
	}
	
}

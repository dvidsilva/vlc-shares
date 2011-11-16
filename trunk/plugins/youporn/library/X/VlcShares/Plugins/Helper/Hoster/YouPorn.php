<?php 

class X_VlcShares_Plugins_Helper_Hoster_YouPorn implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'youporn';
	const PATTERN = '/http:\/\/[w\.]*?youporn\.com\/watch\/(?P<ID>[0-9]+)/i';
	
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
		$http = new Zend_Http_Client("http://www.youporn.com/watch/".$url,
			array(
				'headers' => array(
					'User-Agent' => $this->hide ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' youporn/'.X_VlcShares_Plugins_YouPorn::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
				)
			)
		);
		$http->setParameterPost('user_choice', 'Enter');
		$datas = $http->request(Zend_Http_Client::POST)->getBody();

		$match = array();
		if ( preg_match('/http\:\/\/download\.youporn\.com\/download\/(?P<download>[0-9]+)\?save\=1/i', $datas, $match) < 1) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		$downloadId = $match['download'];
		
		$match = array();
		if ( preg_match('/<title>(?P<title>.*?) - Free Porn Videos - YouPorn<\/title>/i', $datas, $match) ) {
			$title = $match['title'];
		} else {
			$title = '';
		}
		
		$match = array();
		if ( preg_match('/<meta name="description" content="(?P<description>[^\"]*)" \/>/i', $datas, $match) ) {
			$description = $match['description'];
		} else {
			$description = '';
		}
		
		$http->setUri("http://download.youporn.com/download/{$downloadId}?save=1");
		$http->setConfig(array(
				'maxredirects'	=> 0,
			));
		$http->setParameterPost('user_choice', 'Enter');
		$downloadUrl = $http->request(Zend_Http_Client::POST)->getHeader('Location');
		
		$idT1 = substr($url, 0, 2);
		$idT2 = substr($url, 2, 2);
		
		$infos = array(
			'title' => $title,
			'description' => $description,
			'length' => 0,
			'thumbnail' => "http://ss-1.youporn.com/screenshot/{$idT1}/{$idT2}/screenshot/{$url}_extra_large.jpg",
			'url' => $downloadUrl,
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.youporn.com/watch/$playableId";
	}
	
}

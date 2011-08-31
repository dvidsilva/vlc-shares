<?php 


class X_VlcShares_Plugins_Helper_Hoster_Megaupload implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'megaupload';
	const PATTERN = '/http\:\/\/((www\.)?)(megavideo|megaupload)\.com\/\?d\=(?P<ID>[A-Za-z0-9]+)/';
	
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
				return substr($matches['ID'], 0, 8);
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
		// $url is an id now for sure
		/* @var $megauploadHelper X_VlcShares_Plugins_Helper_Megaupload */
		$megauploadHelper = X_VlcShares_Plugins::helpers()->helper('megaupload');
		if ( $megauploadHelper->setMegauploadLocation($url)->getServer() != '' ) {
			return $megauploadHelper->getUrl();
		}
		throw new Exception("Invalid video id", self::E_ID_INVALID);
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

		// $url is an id now for sure
		/* @var $megauploadHelper X_VlcShares_Plugins_Helper_Megaupload */
		$megauploadHelper = X_VlcShares_Plugins::helpers()->helper('megaupload');
		if ( $megauploadHelper->setMegauploadLocation($url)->getServer() == '' ) {
			throw new Exception("Invalid video", self::E_ID_INVALID);
		}
		
		// use cached values
		$infos = array(
			'title' => $megauploadHelper->getTitle(),
			'description' => $megauploadHelper->getDescription(),
			'length' => $megauploadHelper->getDuration(),
		);
		
		return $infos;
		
	}	

	function getHosterUrl($playableId) {
		return "http://www.megavideo.com/?d=$playableId";
	}
	
	
}

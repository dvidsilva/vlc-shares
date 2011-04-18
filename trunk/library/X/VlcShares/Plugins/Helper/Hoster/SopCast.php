<?php 

class X_VlcShares_Plugins_Helper_Hoster_SopCast implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'sopcast';
	// sop://211.152.36.38:3912/15106
	const PATTERN = '/^(?P<ID>sop\:\/\/[^\/]*\/.*)/';
	
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
		return $url;
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
		
		$infos = array(
			'title' => '',
			'description' => '',
			'length' => 0,
			'thumbnail' => '',
		);
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "$playableId";
	}
	
}

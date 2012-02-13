<?php 

class X_VlcShares_Plugins_Helper_Hoster_RealDebridRapidShare extends X_VlcShares_Plugins_Helper_Hoster_RealDebridAbstract {
	
	const ID = 'rapidshare-realdebrid';
	const PATTERN = '/https?\:\/\/((www\.)?)rapidshare\.com\/files\/(?P<ID>.+)/';
	
	/**
	 * give the hoster id
	 * @return string
	 */
	function getId() {
		try {
			return $this->getParentProperty('Id');
		} catch (Exception $e) {
			return self::ID;
		}
	}
	
	/**
	 * get the hoster pattern for regex match
	 * @return string
	 */
	function getPattern() {
		try {
			return $this->getParentProperty('Pattern');
		} catch (Exception $e) {
			return self::PATTERN;
		}
	}
	
	/**
	 * get the resource ID for the hoster
	 * from an $url
	 * @param string $url the hoster page
	 * @return string the resource id
	 */
	function getResourceId($url) {
		try {
			return parent::getResourceId($url);
		} catch (Exception $e) {
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
		try {
			return parent::getPlayableInfos($url, $isId);
		} catch (Exception $e) {
			// special, link can be valid, but i don't have infos
			if ( $e->getCode() == self::EXCEPTION_NOPARENTHOSTER  ) {
				return array(
					'title' => null,
					'description' => null,
				);
			} else {
				throw $e;
			}
		}
	}	
	
	function getHosterUrl($playableId) {
		try {
			return parent::getHosterUrl($playableId);
		} catch (Exception $e) {
			return "http://rapidshare.com/files/$playableId";
		}
	}
	
	
}
